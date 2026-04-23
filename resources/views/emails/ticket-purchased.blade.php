<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VolleyPass biļete</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f9fafb; margin: 0; padding: 0; color: #1f2937; }
    .wrapper { max-width: 560px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
    .header { background: linear-gradient(135deg, #f97316 0%, #3b82f6 100%); padding: 32px 24px; text-align: center; }
    .header h1 { color: #fff; font-size: 24px; margin: 0 0 4px; }
    .header p { color: rgba(255,255,255,0.85); margin: 0; font-size: 14px; }
    .body { padding: 28px 24px; }
    .match-box { background: #f3f4f6; border-radius: 8px; padding: 16px 20px; margin-bottom: 20px; text-align: center; }
    .match-teams { font-size: 20px; font-weight: bold; color: #111827; margin-bottom: 6px; }
    .match-meta { font-size: 13px; color: #6b7280; }
    .seats-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px; }
    .seats-table th { background: #f3f4f6; text-align: left; padding: 8px 12px; color: #374151; font-weight: 600; }
    .seats-table td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; }
    .total-row { font-weight: bold; font-size: 15px; }
    .footer { text-align: center; padding: 20px 24px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; }
    .claim-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; }
    .claim-box p { margin: 0 0 6px; font-size: 13px; color: #1d4ed8; font-weight: 600; }
    .claim-code { font-family: monospace; font-size: 14px; letter-spacing: 1px; color: #1e40af; word-break: break-all; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>VolleyPass</h1>
      <p>Pirkuma apstiprinājums</p>
    </div>
    <div class="body">
      <p>Paldies par pirkumu! Šeit ir informācija par jūsu biļeti.</p>

      <div class="match-box">
        <div class="match-teams">{{ $match->home_team_name }} vs {{ $match->away_team_name }}</div>
        <div class="match-meta">
          {{ \Carbon\Carbon::parse($match->start_time)->format('d.m.Y H:i') }}
          @if($match->location) · {{ $match->location }} @endif
        </div>
      </div>

      @if(count($seats))
      @php
        $originalTotal = collect($seats)->sum(fn($s) => (float)($s['price'] ?? 0));
        $amountPaid = (float) $ticket->amount_paid;
        $discountAmount = round(max(0, $originalTotal - $amountPaid), 2);
      @endphp
      <table class="seats-table">
        <thead>
          <tr>
            <th>Sēdvieta</th>
            <th>Rinda</th>
            <th>Cena</th>
          </tr>
        </thead>
        <tbody>
          @foreach($seats as $s)
            <tr>
              <td>#{{ $s['seat_id'] ?? '—' }}</td>
              <td>—</td>
              <td>€{{ number_format(($s['price'] ?? 0), 2) }}</td>
            </tr>
          @endforeach
          @if($discountAmount > 0)
          <tr>
            <td colspan="2" style="color:#16a34a;">Atlaide</td>
            <td style="color:#16a34a;">−€{{ number_format($discountAmount, 2) }}</td>
          </tr>
          @endif
          <tr class="total-row">
            <td colspan="2">Kopā samaksāts</td>
            <td>€{{ number_format($amountPaid, 2) }}</td>
          </tr>
        </tbody>
      </table>
      @endif

      @if($ticket->claim_token)
      <div class="claim-box">
        <p>Biļetes pieprasīšanas kods</p>
        <span class="claim-code">{{ $ticket->claim_token }}</span>
      </div>
      @endif

      <p style="font-size:13px;color:#6b7280;">Parādiet šo e-pastu vai kodu pie ieejas. Pasūtījuma Nr.: <strong>#{{ $ticket->id }}</strong></p>
    </div>
    <div class="footer">
      © {{ date('Y') }} VolleyPass · Šis ir automātisks paziņojums, lūdzu, neatbildiet uz to.
    </div>
  </div>
</body>
</html>
