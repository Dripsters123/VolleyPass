<?php

namespace Database\Seeders;

use App\Models\Arena;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArenaSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->command->warn('ArenaSeeder: no admin user found – skipping.');
            return;
        }

        foreach ($this->layouts() as $layout) {
            Arena::firstOrCreate(
                ['name' => $layout['name'], 'user_id' => $admin->id],
                array_merge($layout, ['user_id' => $admin->id, 'is_public' => true])
            );
        }

        $this->command->info('Default arena layouts seeded.');
    }

    /* ─────────────────────────────────────────────────────────────
       Helper – generate a row of seats
       ───────────────────────────────────────────────────────────── */
    private function rowSeats(int &$num, int $count, int $startX, int $y, int $step = 50, int $w = 44, int $h = 44): array
    {
        $seats = [];
        for ($i = 0; $i < $count; $i++) {
            $seats[] = [
                'type'   => 'seat',
                'id'     => 's' . $num,
                'number' => $num,
                'x'      => $startX + $i * $step,
                'y'      => $y,
                'width'  => $w,
                'height' => $h,
            ];
            $num++;
        }
        return $seats;
    }

    /* ─────────────────────────────────────────────────────────────
       Helper – generate a column of seats
       ───────────────────────────────────────────────────────────── */
    private function colSeats(int &$num, int $count, int $x, int $startY, int $step = 50, int $w = 44, int $h = 44): array
    {
        $seats = [];
        for ($i = 0; $i < $count; $i++) {
            $seats[] = [
                'type'   => 'seat',
                'id'     => 's' . $num,
                'number' => $num,
                'x'      => $x,
                'y'      => $startY + $i * $step,
                'width'  => $w,
                'height' => $h,
            ];
            $num++;
        }
        return $seats;
    }

    /* ─────────────────────────────────────────────────────────────
       Default layouts
       ───────────────────────────────────────────────────────────── */
    private function layouts(): array
    {
        /* ── Layout 1: Full 6v6 Arena ─────────────────────────── */
        $n1 = 1;
        $court1 = ['type' => 'court', 'id' => 'court', 'x' => 300, 'y' => 200, 'width' => 400, 'height' => 300, 'label' => 'Volejbola laukums'];
        $el1 = array_merge(
            [$court1],
            // North: 2 rows × 8
            $this->rowSeats($n1, 8, 304, 96),
            $this->rowSeats($n1, 8, 304, 146),
            // South: 2 rows × 8
            $this->rowSeats($n1, 8, 304, 510),
            $this->rowSeats($n1, 8, 304, 560),
            // West: 2 cols × 6
            $this->colSeats($n1, 6, 150, 200),
            $this->colSeats($n1, 6, 200, 200),
            // East: 2 cols × 6
            $this->colSeats($n1, 6, 710, 200),
            $this->colSeats($n1, 6, 760, 200),
        );

        /* ── Layout 2: Compact 4v4 Arena ─────────────────────── */
        $n2 = 1;
        $court2 = ['type' => 'court', 'id' => 'court', 'x' => 200, 'y' => 125, 'width' => 350, 'height' => 250, 'label' => 'Volejbola laukums'];
        $el2 = array_merge(
            [$court2],
            // South: 2 rows × 10
            $this->rowSeats($n2, 10, 100, 385),
            $this->rowSeats($n2, 10, 100, 435),
            // East: 1 col × 5
            $this->colSeats($n2, 5, 560, 125),
        );

        /* ── Layout 3: Beach Volleyball 2v2 ──────────────────── */
        $n3 = 1;
        $court3 = ['type' => 'court', 'id' => 'court', 'x' => 150, 'y' => 100, 'width' => 500, 'height' => 280, 'label' => 'Pludmales laukums'];
        $el3 = array_merge(
            [$court3],
            // South only: 1 row × 8
            $this->rowSeats($n3, 8, 150, 390),
        );

        /* ── Layout 4: Tournament Hall (4 large stands) ────────── */
        $n4 = 1;
        $court4 = ['type' => 'court', 'id' => 'court', 'x' => 350, 'y' => 250, 'width' => 500, 'height' => 350, 'label' => 'Volejbola laukums'];
        $el4 = array_merge(
            [$court4],
            // North: 3 rows × 10
            $this->rowSeats($n4, 10, 350, 95),
            $this->rowSeats($n4, 10, 350, 145),
            $this->rowSeats($n4, 10, 350, 195),
            // South: 3 rows × 10
            $this->rowSeats($n4, 10, 350, 615),
            $this->rowSeats($n4, 10, 350, 665),
            $this->rowSeats($n4, 10, 350, 715),
            // West: 3 cols × 7
            $this->colSeats($n4, 7, 150, 250),
            $this->colSeats($n4, 7, 200, 250),
            $this->colSeats($n4, 7, 250, 250),
            // East: 3 cols × 7
            $this->colSeats($n4, 7, 860, 250),
            $this->colSeats($n4, 7, 910, 250),
            $this->colSeats($n4, 7, 960, 250),
        );

        return [
            [
                'name'        => 'Olimpijas halle (6v6)',
                'description' => 'Pilna izmēra volejbola arēna ar 4 tribīnēm — 56 vietas.',
                'width'       => 1000,
                'height'      => 700,
                'elements'    => $el1,
                'layout'      => [],
            ],
            [
                'name'        => 'Kompaktā halle (4v4)',
                'description' => 'Vidēja izmēra halle 4v4 spēlēm — 25 vietas.',
                'width'       => 750,
                'height'      => 540,
                'elements'    => $el2,
                'layout'      => [],
            ],
            [
                'name'        => 'Pludmales volejbols (2v2)',
                'description' => 'Āra pludmales volejbola arēna — 8 tribīnes vietas.',
                'width'       => 800,
                'height'      => 500,
                'elements'    => $el3,
                'layout'      => [],
            ],
            [
                'name'        => 'Turnīru zāle',
                'description' => 'Liela turnīru halle ar 4 plašām tribīnēm — 102 vietas.',
                'width'       => 1200,
                'height'      => 860,
                'elements'    => $el4,
                'layout'      => [],
            ],
        ];
    }
}
