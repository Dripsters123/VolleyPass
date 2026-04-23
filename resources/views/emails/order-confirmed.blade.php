<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pasūtījums apstiprināts</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f9fafb; margin: 0; padding: 0; color: #1f2937; }
    .wrapper { max-width: 560px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
    .header { background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%); padding: 32px 24px; text-align: center; }
    .header h1 { color: #fff; font-size: 24px; margin: 0 0 4px; }
    .header p { color: rgba(255,255,255,0.85); margin: 0; font-size: 14px; }
    .body { padding: 28px 24px; }
    .product-box { background: #f3f4f6; border-radius: 8px; padding: 16px 20px; margin-bottom: 20px; }
    .product-title { font-size: 18px; font-weight: bold; color: #111827; margin-bottom: 4px; }
    .product-meta { font-size: 13px; color: #6b7280; }
    .details-table { width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 20px; }
    .details-table td { padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
    .details-table td:last-child { text-align: right; font-weight: 600; }
    .footer { text-align: center; padding: 20px 24px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>VolleyPass</h1>
      <p>Pasūtījums apstiprināts</p>
    </div>
    <div class="body">
      <p>Paldies par pirkumu! Jūsu pasūtījums ir saņemts un tiek apstrādāts.</p>

      <div class="product-box">
        <div class="product-title">{{ $order->product->title ?? 'Produkts' }}</div>
        @if($order->product->description ?? false)
          <div class="product-meta">{{ Str::limit($order->product->description, 120) }}</div>
        @endif
      </div>

      <table class="details-table">
        <tr>
          <td>Pasūtījuma Nr.</td>
          <td>#{{ $order->id }}</td>
        </tr>
        <tr>
          <td>Statuss</td>
          <td>Apmaksāts</td>
        </tr>
        <tr>
          <td>Summa</td>
          <td>€{{ number_format($order->amount / 100, 2) }}</td>
        </tr>
        <tr>
          <td>Datums</td>
          <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d.m.Y H:i') }}</td>
        </tr>
      </table>

      <p style="font-size:13px;color:#6b7280;">Ja jums ir jautājumi, sazinieties ar mums atbildot uz šo e-pastu.</p>
    </div>
    <div class="footer">
      © {{ date('Y') }} VolleyPass · Šis ir automātisks paziņojums, lūdzu, neatbildiet uz to.
    </div>
  </div>
</body>
</html>
