"""
HoYoLAB Microservice ? FastAPI + genshin.py
Mengambil data Battle Chronicle karakter, Real-Time Notes (Resin/Expedisi),
dan Auto Daily Check-in dari HoYoLAB API.
"""

from fastapi import FastAPI, HTTPException, status
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
import genshin
import asyncio
import logging

# ??? Konfigurasi Logging ????????????????????????????????????????????????????
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s ? %(message)s",
)
logger = logging.getLogger("hoyolab-service")

# ??? Inisialisasi Aplikasi FastAPI ??????????????????????????????????????????
app = FastAPI(
    title="HoYoLAB Microservice",
    description="Microservice untuk mengambil data Battle Chronicle, Real-Time Notes, dan Daily Check-in Genshin Impact dari HoYoLAB.",
    version="1.1.0",
    docs_url="/docs",
    redoc_url="/redoc",
)

# ??? CORS Middleware ?????????????????????????????????????????????????????????
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://localhost",
        "http://localhost:8000",
        "http://127.0.0.1:8000",
    ],
    allow_credentials=True,
    allow_methods=["POST", "GET"],
    allow_headers=["*"],
)


# ??? Pydantic Schemas ????????????????????????????????????????????????????????

class HoyoCredentialsRequest(BaseModel):
    """Body request yang diterima dari Laravel."""
    ltuid_v2: str = Field(..., description="Cookie ltuid_v2 dari HoYoLAB")
    ltoken_v2: str = Field(..., description="Cookie ltoken_v2 dari HoYoLAB")
    uid: int = Field(..., description="UID akun Genshin Impact in-game", gt=0)

    model_config = {
        "json_schema_extra": {
            "examples": [
                {
                    "ltuid_v2": "123456789",
                    "ltoken_v2": "v2_xxxxxxxxxxxxxxxxxxxx",
                    "uid": 812345678,
                }
            ]
        }
    }


class WeaponDetail(BaseModel):
    name: str
    rarity: int
    refinement: int = 1
    level: int = 1
    ascension: int = 0
    type: str = "Sword"
    icon: str = ""


class ArtifactDetail(BaseModel):
    id: int = 0
    name: str = ""
    pos: int = 1
    pos_name: str = ""
    rarity: int = 5
    level: int = 0
    icon: str = ""
    set_name: str = ""
    main_stat_name: str = ""
    main_stat_value: str = ""


class CharacterDetail(BaseModel):
    id: int
    name: str
    element: str
    rarity: int
    level: int
    friendship: int
    constellation: int
    image: str
    weapon: WeaponDetail | None = None
    artifacts: list[ArtifactDetail] = []


class BattleChronicleResponse(BaseModel):
    uid: int
    total_characters: int
    characters: list[CharacterDetail]


# ??? Schemas untuk Notes & Daily Check-in ????????????????????????????????????

class ExpeditionDetail(BaseModel):
    character_icon: str
    status: str
    remaining_time_seconds: int


class RealtimeNotesResponse(BaseModel):
    uid: int
    current_resin: int
    max_resin: int
    remaining_resin_recovery_seconds: int
    current_realm_currency: int
    max_realm_currency: int
    remaining_realm_currency_recovery_seconds: int
    completed_commissions: int
    max_commissions: int
    claimed_commission_reward: bool
    remaining_resin_discounts: int
    max_resin_discounts: int
    transformer_recovery_time_seconds: int | None = None
    transformer_reached: bool = False
    expeditions: list[ExpeditionDetail] = []


class CheckinRewardDetail(BaseModel):
    name: str
    amount: int
    icon: str


class CheckinClaimResponse(BaseModel):
    success: bool
    already_claimed: bool
    message: str
    claimed_rewards_count: int
    reward: CheckinRewardDetail | None = None


class RecentClaimItem(BaseModel):
    name: str
    amount: int
    icon: str
    time: str


class CheckinStatusResponse(BaseModel):
    is_signed_in: bool
    total_claimed_days: int
    recent_claims: list[RecentClaimItem] = []


def create_client(ltuid_v2: str, ltoken_v2: str) -> genshin.Client:
    cookies = {
        "ltuid_v2": ltuid_v2,
        "ltoken_v2": ltoken_v2,
    }
    client = genshin.Client(cookies=cookies)
    client.default_game = genshin.Game.GENSHIN
    return client


# ??? Endpoint Utama ??????????????????????????????????????????????????????????

@app.get("/health", tags=["Health"])
async def health_check():
    """Endpoint pengecekan status microservice."""
    return {"status": "ok", "service": "hoyolab-service"}


@app.post(
    "/api/genshin/characters",
    response_model=BattleChronicleResponse,
    status_code=status.HTTP_200_OK,
    tags=["Genshin Impact"],
    summary="Ambil daftar karakter dari HoYoLAB",
)
async def get_genshin_characters(body: HoyoCredentialsRequest):
    """
    Menerima kredensial cookie HoYoLAB dan UID akun, lalu mengambil
    data Battle Chronicle (daftar karakter + senjata + level) secara langsung.
    """
    logger.info(f"Menerima request karakter untuk UID: {body.uid}")
    client = create_client(body.ltuid_v2, body.ltoken_v2)

    try:
        try:
            detailed_data = await client.get_genshin_detailed_characters(body.uid)
            characters = detailed_data.characters
            logger.info(f"Berhasil mengambil {len(characters)} karakter detail untuk UID {body.uid}")
        except Exception as ex_det:
            logger.warning(f"get_genshin_detailed_characters gagal ({ex_det}), fallback ke get_genshin_characters...")
            characters = await client.get_genshin_characters(body.uid)
            logger.info(f"Fallback berhasil mengambil {len(characters)} karakter untuk UID {body.uid}")

        WEAPON_TYPE_MAP = {
            1: "Sword",
            10: "Catalyst",
            11: "Claymore",
            12: "Bow",
            13: "Polearm",
        }

        character_list: list[CharacterDetail] = []
        for char in characters:
            weapon_data = None
            if char.weapon:
                w_type_raw = getattr(char.weapon, "type", 1)
                w_type_name = WEAPON_TYPE_MAP.get(w_type_raw, "Sword") if isinstance(w_type_raw, int) else str(w_type_raw)
                weapon_data = WeaponDetail(
                    name=char.weapon.name,
                    rarity=char.weapon.rarity,
                    refinement=getattr(char.weapon, "refinement", 1),
                    level=char.weapon.level,
                    ascension=getattr(char.weapon, "ascension", getattr(char.weapon, "promote_level", 0)),
                    type=w_type_name,
                    icon=str(getattr(char.weapon, "icon", "") or ""),
                )

            artifact_list: list[ArtifactDetail] = []
            if hasattr(char, "artifacts") and char.artifacts:
                for art in char.artifacts:
                    set_obj = getattr(art, "set", None)
                    s_name = getattr(set_obj, "name", "") if set_obj else ""
                    main_stat_obj = getattr(art, "main_stat", None)
                    ms_val = ""
                    ms_name = ""
                    if main_stat_obj:
                        ms_val = str(getattr(main_stat_obj, "value", "") or "")
                        info_obj = getattr(main_stat_obj, "info", None)
                        if info_obj:
                            ms_name = str(getattr(info_obj, "name", "") or "")

                    artifact_list.append(
                        ArtifactDetail(
                            id=getattr(art, "id", 0),
                            name=getattr(art, "name", ""),
                            pos=getattr(art, "pos", 1),
                            pos_name=getattr(art, "pos_name", ""),
                            rarity=getattr(art, "rarity", 5),
                            level=getattr(art, "level", 0),
                            icon=str(getattr(art, "icon", "") or ""),
                            set_name=s_name,
                            main_stat_name=ms_name,
                            main_stat_value=ms_val,
                        )
                    )

            character_list.append(
                CharacterDetail(
                    id=char.id,
                    name=char.name,
                    element=char.element.name if hasattr(char.element, "name") else str(char.element),
                    rarity=char.rarity,
                    level=char.level,
                    friendship=getattr(char, "friendship", 1),
                    constellation=getattr(char, "constellation", 0),
                    image=getattr(char, "icon", getattr(char, "image", "")),
                    weapon=weapon_data,
                    artifacts=artifact_list,
                )
            )

        return BattleChronicleResponse(
            uid=body.uid,
            total_characters=len(character_list),
            characters=character_list,
        )

    except genshin.errors.InvalidCookies as e:
        logger.warning(f"Cookie tidak valid untuk UID {body.uid}: {e}")
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail={
                "error": "INVALID_COOKIES",
                "message": "Cookie ltuid_v2 atau ltoken_v2 tidak valid atau sudah expired. Silakan perbarui cookie dari HoYoLAB.",
            },
        )
    except genshin.errors.AccountNotFound as e:
        logger.warning(f"Akun tidak ditemukan untuk UID {body.uid}: {e}")
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail={
                "error": "ACCOUNT_NOT_FOUND",
                "message": f"Akun Genshin Impact dengan UID {body.uid} tidak ditemukan.",
            },
        )
    except genshin.errors.DataNotPublic as e:
        logger.warning(f"Data Battle Chronicle UID {body.uid} tidak publik: {e}")
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail={
                "error": "DATA_NOT_PUBLIC",
                "message": "Data Battle Chronicle akun ini tidak publik. Aktifkan 'Show Battle Chronicle' di pengaturan HoYoLAB.",
            },
        )
    except genshin.errors.GenshinException as e:
        logger.error(f"Error dari HoYoverse API: {e} (retcode: {e.retcode})")
        raise HTTPException(
            status_code=status.HTTP_502_BAD_GATEWAY,
            detail={
                "error": "HOYOVERSE_API_ERROR",
                "message": f"HoYoverse API mengembalikan error: {e.msg}",
                "retcode": e.retcode,
            },
        )
    except Exception as e:
        logger.exception(f"Error tidak terduga untuk UID {body.uid}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail={
                "error": "INTERNAL_ERROR",
                "message": "Terjadi error internal pada microservice.",
            },
        )


@app.post(
    "/api/genshin/notes",
    response_model=RealtimeNotesResponse,
    status_code=status.HTTP_200_OK,
    tags=["Genshin Impact"],
    summary="Ambil data Real-Time Notes (Resin, Realm Currency, Expedisi)",
)
async def get_genshin_realtime_notes(body: HoyoCredentialsRequest):
    """
    Mengambil data Real-Time Notes (Resin saat ini, max resin, waktu pemulihan,
    realm currency, daily commissions, dan ekspedisi karakter) untuk UID terkait.
    """
    logger.info(f"Menerima request Real-Time Notes untuk UID: {body.uid}")
    client = create_client(body.ltuid_v2, body.ltoken_v2)

    try:
        notes = await client.get_genshin_notes(body.uid)

        # Expedisi
        expedition_list: list[ExpeditionDetail] = []
        for exp in notes.expeditions:
            remaining_secs = 0
            if exp.remaining_time:
                remaining_secs = int(exp.remaining_time.total_seconds())

            expedition_list.append(
                ExpeditionDetail(
                    character_icon=str(getattr(exp, "character_icon", "") or ""),
                    status=str(getattr(exp, "status", "Ongoing")),
                    remaining_time_seconds=remaining_secs,
                )
            )

        # Transformer
        transformer_secs = None
        transformer_reached = False
        if notes.remaining_transformer_recovery_time is not None:
            transformer_secs = int(notes.remaining_transformer_recovery_time.total_seconds())
            transformer_reached = transformer_secs <= 0
        else:
            transformer_reached = True

        return RealtimeNotesResponse(
            uid=body.uid,
            current_resin=notes.current_resin,
            max_resin=notes.max_resin,
            remaining_resin_recovery_seconds=int(notes.remaining_resin_recovery_time.total_seconds()) if notes.remaining_resin_recovery_time else 0,
            current_realm_currency=notes.current_realm_currency,
            max_realm_currency=notes.max_realm_currency,
            remaining_realm_currency_recovery_seconds=int(notes.remaining_realm_currency_recovery_time.total_seconds()) if notes.remaining_realm_currency_recovery_time else 0,
            completed_commissions=notes.completed_commissions,
            max_commissions=notes.max_commissions,
            claimed_commission_reward=notes.claimed_commission_reward,
            remaining_resin_discounts=notes.remaining_resin_discounts,
            max_resin_discounts=notes.max_resin_discounts,
            transformer_recovery_time_seconds=transformer_secs,
            transformer_reached=transformer_reached,
            expeditions=expedition_list,
        )

    except genshin.errors.InvalidCookies as e:
        logger.warning(f"Cookie tidak valid untuk UID {body.uid}: {e}")
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail={
                "error": "INVALID_COOKIES",
                "message": "Cookie ltuid_v2 atau ltoken_v2 tidak valid atau sudah expired.",
            },
        )
    except genshin.errors.AccountNotFound as e:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail={
                "error": "ACCOUNT_NOT_FOUND",
                "message": f"Akun Genshin Impact dengan UID {body.uid} tidak ditemukan.",
            },
        )
    except genshin.errors.DataNotPublic as e:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail={
                "error": "DATA_NOT_PUBLIC",
                "message": "Data Real-Time Notes akun ini tidak publik atau belum diaktifkan di HoYoLAB Widget settings.",
            },
        )
    except genshin.errors.GenshinException as e:
        logger.error(f"Error HoYoverse API Notes: {e} (retcode: {e.retcode})")
        raise HTTPException(
            status_code=status.HTTP_502_BAD_GATEWAY,
            detail={
                "error": "HOYOVERSE_API_ERROR",
                "message": f"HoYoverse API mengembalikan error: {e.msg}",
                "retcode": e.retcode,
            },
        )
    except Exception as e:
        logger.exception(f"Error tidak terduga pada Notes untuk UID {body.uid}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail={
                "error": "INTERNAL_ERROR",
                "message": f"Terjadi error internal saat mengambil notes: {str(e)}",
            },
        )


@app.post(
    "/api/genshin/daily-checkin",
    response_model=CheckinClaimResponse,
    status_code=status.HTTP_200_OK,
    tags=["Genshin Impact"],
    summary="Klaim Daily Check-in HoYoLAB",
)
async def claim_genshin_daily_checkin(body: HoyoCredentialsRequest):
    """
    Melakukan klaim check-in harian HoYoLAB untuk akun Genshin Impact.
    Mengembalikan informasi hadiah yang didapat jika berhasil, atau notifikasi jika sudah diklaim.
    """
    logger.info(f"Menerima request Daily Check-in untuk UID: {body.uid}")
    client = create_client(body.ltuid_v2, body.ltoken_v2)

    try:
        reward_info = await client.get_reward_info()

        try:
            reward = await client.claim_daily_reward()
            logger.info(f"Berhasil claim reward: {reward.name} x{reward.amount} untuk UID {body.uid}")
            return CheckinClaimResponse(
                success=True,
                already_claimed=False,
                message=f"Check-in berhasil! Mendapatkan {reward.name} x{reward.amount}.",
                claimed_rewards_count=reward_info.claimed_rewards + 1,
                reward=CheckinRewardDetail(
                    name=reward.name,
                    amount=reward.amount,
                    icon=str(reward.icon),
                ),
            )
        except genshin.errors.AlreadyClaimed:
            logger.info(f"UID {body.uid} sudah melakukan check-in hari ini.")
            return CheckinClaimResponse(
                success=True,
                already_claimed=True,
                message="Kamu sudah melakukan check-in hari ini!",
                claimed_rewards_count=reward_info.claimed_rewards,
                reward=None,
            )

    except genshin.errors.InvalidCookies as e:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail={
                "error": "INVALID_COOKIES",
                "message": "Cookie ltuid_v2 atau ltoken_v2 tidak valid atau sudah expired.",
            },
        )
    except genshin.errors.GenshinException as e:
        logger.error(f"Error HoYoverse API Check-in: {e} (retcode: {e.retcode})")
        raise HTTPException(
            status_code=status.HTTP_502_BAD_GATEWAY,
            detail={
                "error": "HOYOVERSE_API_ERROR",
                "message": f"HoYoverse API mengembalikan error: {e.msg}",
                "retcode": e.retcode,
            },
        )
    except Exception as e:
        logger.exception(f"Error tidak terduga pada Check-in untuk UID {body.uid}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail={
                "error": "INTERNAL_ERROR",
                "message": f"Terjadi error internal saat check-in: {str(e)}",
            },
        )


@app.post(
    "/api/genshin/daily-checkin/status",
    response_model=CheckinStatusResponse,
    status_code=status.HTTP_200_OK,
    tags=["Genshin Impact"],
    summary="Ambil status dan riwayat Daily Check-in",
)
async def get_genshin_daily_checkin_status(body: HoyoCredentialsRequest):
    """
    Mengambil status check-in hari ini serta riwayat 7 hadiah check-in terakhir.
    """
    logger.info(f"Menerima request status Daily Check-in untuk UID: {body.uid}")
    client = create_client(body.ltuid_v2, body.ltoken_v2)

    try:
        reward_info = await client.get_reward_info()

        recent_claims: list[RecentClaimItem] = []
        try:
            async for claim in client.claimed_rewards(limit=7):
                recent_claims.append(
                    RecentClaimItem(
                        name=claim.name,
                        amount=claim.amount,
                        icon=str(claim.icon),
                        time=str(claim.time),
                    )
                )
        except Exception as ex_claims:
            logger.warning(f"Gagal mengambil riwayat reward: {ex_claims}")

        return CheckinStatusResponse(
            is_signed_in=reward_info.signed_in,
            total_claimed_days=reward_info.claimed_rewards,
            recent_claims=recent_claims,
        )

    except genshin.errors.InvalidCookies as e:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail={
                "error": "INVALID_COOKIES",
                "message": "Cookie ltuid_v2 atau ltoken_v2 tidak valid atau sudah expired.",
            },
        )
    except genshin.errors.GenshinException as e:
        raise HTTPException(
            status_code=status.HTTP_502_BAD_GATEWAY,
            detail={
                "error": "HOYOVERSE_API_ERROR",
                "message": f"HoYoverse API mengembalikan error: {e.msg}",
                "retcode": e.retcode,
            },
        )
    except Exception as e:
        logger.exception(f"Error tidak terduga pada status Check-in UID {body.uid}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail={
                "error": "INTERNAL_ERROR",
                "message": f"Terjadi error internal saat mengambil status check-in: {str(e)}",
            },
        )
