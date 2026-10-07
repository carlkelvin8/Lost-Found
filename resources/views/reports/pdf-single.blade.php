<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { size: A4 portrait; margin: 12mm 10mm 14mm 10mm; }
  * { font-family: DejaVu Sans, sans-serif; box-sizing: border-box; }
  body { font-size: 8.5px; color: #1e293b; line-height: 1.5; margin: 0; }
  .accent-bar { height: 3px; background: #0041C7; margin: -12mm -10mm 0 -10mm; }
  .header-table { width: 100%; border-collapse: collapse; margin: 8px 0 8px 0; }
  .header-table td { vertical-align: middle; padding: 0; border: none; }
  .logo-cell { width: 48px; }
  .logo-cell img { width: 40px; height: 40px; object-fit: contain; }
  .org-name { font-size: 9.5px; font-weight: 800; color: #0041C7; margin: 0; letter-spacing: -0.02em; }
  .org-sub { font-size: 6px; color: #64748b; margin: 1px 0 0 0; text-transform: uppercase; letter-spacing: .05em; font-weight: 600; }
  .ref-badge { display: inline-block; background: #0041C7; color: white; font-weight: 800; font-size: 9px; padding: 4px 8px; border-radius: 4px; letter-spacing: .02em; }
  .contact-box { text-align: right; font-size: 6.5px; color: #64748b; line-height: 1.4; }
  .divider { height: 1px; background: #e2e8f0; margin: 6px 0 10px 0; }
  .title-main { font-size: 14px; font-weight: 800; color: #0f172a; margin: 0; }
  .title-meta { font-size: 7px; color: #475569; margin-top: 3px; }
  .badge { display: inline-block; padding: 3px 7px; border-radius: 4px; font-size: 6.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; vertical-align: middle; }
  .b-lost { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
  .b-found { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
  .s-pending { background: #fef3c7; color: #92400e; }
  .s-matched { background: #dbeafe; color: #1e40af; }
  .s-claimed { background: #ede9fe; color: #6d28d9; }
  .s-returned { background: #d1fae5; color: #065f46; }
  .s-archived { background: #e2e8f0; color: #475569; }
  .card { border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 10px; overflow: hidden; page-break-inside: avoid; }
  .card-head { background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 6px 10px; font-weight: 800; font-size: 7.5px; text-transform: uppercase; letter-spacing: .06em; color: #334155; }
  .card-head .icon { display: inline-block; width: 14px; height: 14px; background: #0041C7; color: white; border-radius: 3px; text-align: center; line-height: 14px; font-size: 8px; margin-right: 4px; vertical-align: middle; }
  .card-body { padding: 10px 12px; }
  .info-table { width: 100%; border-collapse: collapse; }
  .info-table td { padding: 3px 8px 3px 0; vertical-align: top; width: 50%; }
  .label { font-size: 6px; text-transform: uppercase; letter-spacing: .06em; color: #64748b; font-weight: 700; margin-bottom: 1px; }
  .value { font-size: 8.5px; font-weight: 600; color: #0f172a; margin-bottom: 6px; word-wrap: break-word; overflow-wrap: anywhere; }
  .value-large { font-size: 11px; font-weight: 800; color: #0f172a; }
  .desc-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 7px 8px; margin-top: 4px; }
  .desc { font-size: 8px; color: #334155; white-space: pre-wrap; word-wrap: break-word; line-height: 1.5; }
  .img-wrap { text-align: center; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; background: #f8fafc; margin-bottom: 10px; }
  .img-wrap img { max-width: 100%; max-height: 200px; border-radius: 4px; }
  .img-caption { font-size: 6px; color: #94a3b8; margin-top: 4px; text-transform: uppercase; letter-spacing: .04em; }
  .claim-row { border-bottom: 1px solid #e2e8f0; padding: 6px 0; }
  .claim-row:last-child { border-bottom: none; }
  .claim-id { font-size: 7px; font-weight: 800; color: #0041C7; }
  .claim-meta { font-size: 6.5px; color: #64748b; }
  .footer { position: fixed; bottom: -10mm; left: 0; right: 0; text-align: center; font-size: 6px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 4px; }
  .footer .pagenum:after { content: counter(page) " of " counter(pages); }
  .sign-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
  .sign-table td { width: 33.33%; text-align: center; vertical-align: bottom; padding: 0 10px; }
  .sign-line { border-top: 1px solid #334155; margin: 32px 10px 0 10px; padding-top: 4px; }
  .sign-role { font-size: 6px; text-transform: uppercase; letter-spacing: .06em; color: #64748b; font-weight: 700; }
  .sign-name { font-size: 7.5px; font-weight: 700; color: #0f172a; margin-top: 2px; }
  .notice { background: #fef3c7; border: 1px solid #fde68a; border-radius: 4px; padding: 6px 8px; font-size: 6.5px; color: #92400e; margin-top: 10px; text-align: center; }
</style>
</head>
<body>
  <div class="accent-bar"></div>

  <table class="header-table">
    <tr>
      <td class="logo-cell">
        @if(!empty($logoBase64))
          <img src="{{ $logoBase64 }}" alt="NAAP Logo">
        @else
          <div style="width:40px;height:40px;background:#0041C7;border-radius:50%;text-align:center;line-height:40px;color:white;font-weight:800;font-size:13px;">N</div>
        @endif
      </td>
      <td>
        <p class="org-name">National Aviation Academy of the Philippines</p>
        <p class="org-sub">Lost &amp; Found Management System &bull; Office of Student Affairs</p>
      </td>
      <td style="width: 170px;" class="contact-box">
        <div style="font-weight:800;color:#0041C7;font-size:7.5px;letter-spacing:.04em;">OFFICIAL ITEM REPORT</div>
        <div>Piccio Garden, Villamor, Pasay City</div>
      </td>
    </tr>
  </table>

  <table style="width:100%;border-collapse:collapse;margin-top:4px">
    <tr>
      <td style="vertical-align:bottom">
        <p class="title-main">Item Report <span style="color:#0041C7">LF-{{ str_pad($report->id, 6, '0', STR_PAD_LEFT) }}</span></p>
        <p class="title-meta">
          Generated: {{ $generatedAt->format('M d, Y h:i A') }} &nbsp;|&nbsp; By: {{ $generatedBy }} &nbsp;|&nbsp;
          <span class="badge s-{{ $report->status }}">{{ $report->status }}</span> &nbsp;
          <span class="badge {{ $report->report_type==='lost' ? 'b-lost' : 'b-found' }}">{{ $report->report_type }}</span>
        </p>
      </td>
      <td style="text-align:right;vertical-align:bottom">
        <span class="ref-badge">LF-{{ str_pad($report->id, 6, '0', STR_PAD_LEFT) }}</span>
        <div style="font-size:6px;color:#94a3b8;margin-top:2px">System Reference — Do not duplicate</div>
      </td>
    </tr>
  </table>

  <div class="divider"></div>

  @if($imageBase64)
    <div class="img-wrap">
      <img src="{{ $imageBase64 }}" alt="Item image">
      <div class="img-caption">Attached evidence photo — LF-{{ str_pad($report->id, 6, '0', STR_PAD_LEFT) }}</div>
    </div>
  @elseif($report->photos->first()?->photo_url)
    <div style="text-align:center;border:1px dashed #cbd5e1;border-radius:6px;padding:10px;background:#f8fafc;margin-bottom:10px;color:#94a3b8;font-size:7px">
      Photo exists but not embedded (file too large or missing). View in system for full image.
    </div>
  @endif

  <div class="card">
    <div class="card-head"><span class="icon">●</span> Item Information</div>
    <div class="card-body">
      <table class="info-table">
        <tr>
          <td><div class="label">Reference No.</div><div class="value value-large" style="color:#0041C7">LF-{{ str_pad($report->id, 6, '0', STR_PAD_LEFT) }}</div></td>
          <td><div class="label">Report Type</div><div class="value"><span class="badge {{ $report->report_type==='lost' ? 'b-lost' : 'b-found' }}" style="font-size:7px;padding:4px 8px">{{ ucfirst($report->report_type) }}</span></div></td>
        </tr>
      </table>
      <div class="label">Item Name</div><div class="value value-large">{{ $report->item_name ?? '—' }}</div>
      <table class="info-table">
        <tr>
          <td><div class="label">Category</div><div class="value">{{ $report->category?->name ?? '—' }}</div></td>
          <td><div class="label">Brand / Model</div><div class="value">{{ $report->brand_model ?? '—' }}</div></td>
        </tr>
        <tr>
          <td><div class="label">Color / Distinguishing Marks</div><div class="value">{{ $report->color ?? '—' }}</div></td>
          <td><div class="label">Location</div><div class="value">{{ $report->location?->name ?? '—' }}</div></td>
        </tr>
        <tr>
          <td><div class="label">{{ $report->report_type==='lost' ? 'Date Lost' : 'Date Found' }}</div><div class="value">{{ $report->incident_date ? \Carbon\Carbon::parse($report->incident_date)->format('M d, Y') : '—' }} @if($report->incident_time) — {{ $report->incident_time }} @endif</div></td>
          <td><div class="label">Report Date</div><div class="value">{{ $report->created_at?->format('M d, Y h:i A') ?? '—' }}</div></td>
        </tr>
        <tr>
          <td><div class="label">Current Status</div><div class="value"><span class="badge s-{{ $report->status }}">{{ $report->status }}</span></div></td>
          <td><div class="label">Last Updated</div><div class="value">{{ $report->updated_at?->format('M d, Y h:i A') ?? '—' }}</div></td>
        </tr>
      </table>
      <div class="label" style="margin-top:6px">Description</div>
      <div class="desc-box"><div class="desc">{{ $report->item_description ?? 'No description provided.' }}</div></div>
      @if($report->circumstances)
        <div class="label" style="margin-top:8px">Circumstances</div>
        <div class="desc-box"><div class="desc">{{ $report->circumstances }}</div></div>
      @endif
    </div>
  </div>

  <div class="card">
    <div class="card-head"><span class="icon" style="background:#059669">●</span> Reporter / Finder Details</div>
    <div class="card-body">
      <table class="info-table">
        <tr>
          <td><div class="label">Full Name</div><div class="value">{{ $report->reporter?->profile?->full_name ?? $report->reporter?->email ?? '—' }}</div></td>
          <td><div class="label">Email Address</div><div class="value">{{ $report->reporter?->email ?? '—' }}</div></td>
        </tr>
        <tr>
          <td><div class="label">Department / Course</div><div class="value">{{ $report->reporter?->profile?->department?->name ?? '—' }}</div></td>
          <td><div class="label">Contact Override</div><div class="value">{{ $report->contact_override ?? '—' }}</div></td>
        </tr>
      </table>
    </div>
  </div>

  @if($report->claims && $report->claims->count())
    <div class="card">
      <div class="card-head"><span class="icon" style="background:#7c3aed">●</span> Claim History ({{ $report->claims->count() }} claim(s))</div>
      <div class="card-body" style="padding:0 12px">
        @foreach($report->claims as $c)
          <div class="claim-row">
            <table style="width:100%;border-collapse:collapse">
              <tr>
                <td style="width:60%;vertical-align:top">
                  <div class="claim-id">Claim #{{ $c->id }} — <span class="badge s-{{ $c->status }}" style="font-size:6px">{{ $c->status }}</span></div>
                  <div class="desc" style="font-size:7.5px;margin-top:2px">{{ $c->proof_text ? \Illuminate\Support\Str::limit($c->proof_text, 140) : 'No proof text.' }}</div>
                </td>
                <td style="width:40%;vertical-align:top;text-align:right">
                  <div class="label">Claimant</div><div class="value" style="font-size:7.5px">{{ $c->claimant?->profile?->full_name ?? $c->claimant?->email ?? '—' }}</div>
                  <div class="claim-meta">Filed: {{ $c->created_at?->format('M d, Y h:i A') ?? '—' }} @if($c->reviewed_at) <br>Reviewed: {{ $c->reviewed_at->format('M d, Y h:i A') }} @endif</div>
                </td>
              </tr>
            </table>
          </div>
        @endforeach
      </div>
    </div>
  @else
    <div style="border:1px dashed #cbd5e1;border-radius:6px;padding:8px;text-align:center;color:#94a3b8;font-size:7px;background:#f8fafc;margin-bottom:10px">
      No claims have been filed for this report.
    </div>
  @endif

  @if($report->statusHistory && $report->statusHistory->count())
    <div class="card">
      <div class="card-head"><span class="icon" style="background:#d97706">●</span> Status History</div>
      <div class="card-body" style="padding:0">
        <table style="width:100%;border-collapse:collapse;font-size:7px">
          <thead><tr style="background:#f8fafc"><th style="padding:5px 8px;text-align:left;font-size:6px;text-transform:uppercase;letter-spacing:.04em;color:#64748b;border-bottom:1px solid #e2e8f0">Date</th><th style="padding:5px 8px;text-align:left;font-size:6px;text-transform:uppercase;letter-spacing:.04em;color:#64748b;border-bottom:1px solid #e2e8f0">From → To</th><th style="padding:5px 8px;text-align:left;font-size:6px;text-transform:uppercase;letter-spacing:.04em;color:#64748b;border-bottom:1px solid #e2e8f0">Note</th></tr></thead>
          <tbody>
            @foreach($report->statusHistory->take(10) as $h)
              <tr style="border-bottom:1px solid #f1f5f9"><td style="padding:4px 8px;white-space:nowrap">{{ $h->changed_at?->format('M d, Y h:i A') ?? $h->created_at?->format('M d, Y') }}</td><td style="padding:4px 8px"><span class="badge s-{{ $h->old_status ?? 'pending' }}" style="font-size:5.5px">{{ $h->old_status ?? '—' }}</span> → <span class="badge s-{{ $h->new_status }}">{{ $h->new_status }}</span></td><td style="padding:4px 8px;color:#334155">{{ $h->note ?? '—' }}</td></tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

  <div class="notice">
    This document is system-generated by NAAP Lost &amp; Found Management System. Any erasure or alteration voids this report. For verification, contact OSA at lostfound@naap.edu.ph.
  </div>

  <table class="sign-table">
    <tr>
      <td><div class="sign-line"></div><div class="sign-name">Prepared by</div><div class="sign-role">{{ $generatedBy }}</div><div class="sign-name" style="font-size:6px;color:#94a3b8;font-weight:400">OSA Staff — {{ $generatedAt->format('M d, Y') }}</div></td>
      <td><div class="sign-line"></div><div class="sign-name">Verified by</div><div class="sign-role">OSA Head / Designate</div><div class="sign-name" style="font-size:6px;color:#94a3b8;font-weight:400">Signature over printed name</div></td>
      <td><div class="sign-line"></div><div class="sign-name">Noted by</div><div class="sign-role">NAAP Administration</div><div class="sign-name" style="font-size:6px;color:#94a3b8;font-weight:400">Signature over printed name</div></td>
    </tr>
  </table>

  <div class="footer">
    <div>National Aviation Academy of the Philippines — Lost &amp; Found Management System &nbsp;|&nbsp; Confidential — For official use only &nbsp;|&nbsp; Page <span class="pagenum"></span></div>
    <div style="margin-top:1px">Reference: LF-{{ str_pad($report->id, 6, '0', STR_PAD_LEFT) }} &bull; Generated {{ $generatedAt->format('M d, Y h:i A') }} &bull; This is a system-generated report.</div>
  </div>
</body>
</html>
