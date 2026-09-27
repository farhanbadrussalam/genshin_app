"""
HoYoLAB Microservice — FastAPI + genshin.py
Mengambil data Battle Chronicle karakter dari HoYoLAB API.
"""

from fastapi import FastAPI, HTTPException, status
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
import genshin
import asyncio
import logging

# ─── Konfigurasi Logging ────────────────────────────────────────────────────
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s — %(message)s",
)
logger = logging.getLogger("hoyolab-service")

# ─── Inisialisasi Aplikasi FastAPI ──────────────────────────────────────────
app = FastAPI(
    title="HoYoLAB Microservice",
    description="Microservice untuk mengambil data Battle Chronicle karakter Genshin Impact dari HoYoLAB.",
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc",
)

# ─── CORS Middleware ─────────────────────────────────────────────────────────
# Izinkan request dari Laravel local dev server
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


# ─── Pydantic Schemas ────────────────────────────────────────────────────────

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
    """Detail senjata yang diequip karakter."""
    name: str
    rarity: int
    refinement: int
    level: int
    ascension: int


class ConstellationDetail(BaseModel):
    """Detail konstellasi karakter."""
    name: str
    is_activated: bool


class CharacterDetail(BaseModel):
    """Representasi satu karakter dari Battle Chronicle."""
    id: int
    name: str
    element: str
    rarity: int
    level: int
    friendship: int
    constellation: int
    image: str
    weapon: WeaponDetail | None = None


class BattleChronicleResponse(BaseModel):
    """Response utama yang dikirim kembali ke Laravel."""
    uid: int
    total_characters: int
    characters: list[CharacterDetail]


# ─── Endpoint Utama ──────────────────────────────────────────────────────────

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
    data Battle Chronicle (daftar karakter + senjata + level) secara langsung
    dari API HoYoverse menggunakan library genshin.py.

    **Cara mendapatkan cookie:**
    1. Login ke https://www.hoyolab.com
    2. Buka DevTools → Application → Cookies → hoyolab.com
    3. Salin nilai `ltuid_v2` dan `ltoken_v2`
    """
    logger.info(f"Menerima request untuk UID: {body.uid}")

    # ─── Setup genshin.py Client ─────────────────────────────────────────
    cookies = {
        "ltuid_v2": body.ltuid_v2,
        "ltoken_v2": body.ltoken_v2,
    }

    client = genshin.Client(cookies=cookies)
    client.default_game = genshin.Game.GENSHIN

    try:
        # ─── Ambil Data Battle Chronicle ─────────────────────────────────
        logger.info("Menghubungi HoYoLAB API...")
        characters = await client.get_genshin_characters(body.uid)
        logger.info(f"Berhasil mengambil {len(characters)} karakter untuk UID {body.uid}")

        # ─── Serialisasi Data ─────────────────────────────────────────────
        character_list: list[CharacterDetail] = []
        for char in characters:
            weapon_data = None
            if char.weapon:
                weapon_data = WeaponDetail(
                    name=char.weapon.name,
                    rarity=char.weapon.rarity,
                    refinement=char.weapon.refinement,
                    level=char.weapon.level,
                    ascension=char.weapon.ascension,
                )

            character_list.append(
                CharacterDetail(
                    id=char.id,
                    name=char.name,
                    element=char.element.name if hasattr(char.element, "name") else str(char.element),
                    rarity=char.rarity,
                    level=char.level,
                    friendship=char.friendship,
                    constellation=char.constellation,
                    image=char.image,
                    weapon=weapon_data,
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
