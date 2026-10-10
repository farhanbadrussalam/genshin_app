import json, subprocess

p = subprocess.run(['docker', 'exec', 'genshin-app', 'php', 'artisan', 'tinker', '--execute=echo json_encode(DB::table(" materials\)->pluck(\id\,
