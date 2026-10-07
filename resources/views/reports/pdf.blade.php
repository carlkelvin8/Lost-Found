<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { size: A4 landscape; margin: 10mm 8mm 15mm 8mm; }
  * { font-family: DejaVu Sans, sans-serif; box-sizing: border-box; }
  body { font-size: 8px; color: #1e293b; line-height: 1.45; margin: 0; padding: 0; }
  .accent-bar { height: 3px; background: #0041C7; margin: -10mm -8mm 0 -8mm; }
  /* Header table */
  .header-table { width: 100%; border-collapse: collapse; margin: 8px 0 6px 0; }
  .header-table td { vertical-align: middle; padding: 0; border: none; }
  .logo-cell { width: 52px; }
  .logo-cell img { width: 44px; height: 44px; object-fit: contain; }
  .org-name { font-size: 10px; font-weight: 800; color: #0041C7; margin: 0; letter-spacing: -0.02em; line-height: 1.1; }
  .org-sub { font-size: 6.5px; color: #64748b; margin: 1px 0 0 0; text-transform: uppercase; letter-spacing: .06em; font-weight: 600; }
  .title-main { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0; text-align: center; letter-spacing: -0.02em; }
  .title-sub { font-size: 7px; color: #475569; text-align: center; margin-top: 2px; }
  .meta-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
  .meta-table td { font-size: 6.8px; color: #64748b; padding: 1px 0; }
  .meta-table strong { color: #334155; }
  .contact-box { text-align: right; font-size: 6.8px; color: #64748b; line-height: 1.4; }
  .contact-box .brand { font-size: 8px; font-weight: 800; color: #0041C7; }
  /* Divider */
  .divider { height: 1px; background: #e2e8f0; margin: 6px 0 8px 0; }
  /* Summary */
  .summary-table { width: 100%; border-collapse: collapse; margin: 0 0 8px 0; }
  .summary-table td { width: 20%; border: 1px solid #e2e8f0; padding: 7px 6px; text-align: center; vertical-align: middle; }
  .summary-table td:first-child { border-left: 3px solid #0041C7; }
  .summary-table td:nth-child(2) { border-left: 3px solid #d97706; }
  .summary-table td:nth-child(3) { border-left: 3px solid #059669; }
  .summary-table td:nth-child(4) { border-left: 3px solid #7c3aed; }
  .summary-table td:nth-child(5) { border-left: 3px solid #64748b; }
  .s-label { display: block; font-size: 5.8px; text-transform: uppercase; letter-spacing: .07em; color: #64748b; font-weight: 700; }
  .s-value { display: block; font-size: 14px; font-weight: 800; color: #0f172a; margin-top: 2px; line-height: 1; }
  .s-hint { font-size: 5.5px; color: #94a3b8; margin-top: 1px; }
  /* Filters */
  .filters-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 8px; margin-bottom: 8px; }
  .filters-title { font-size: 6.5px; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
  .filters-grid { width: 100%; border-collapse: collapse; }
  .filters-grid td { font-size: 6.8px; padding: 2px 6px 2px 0; vertical-align: top; }
  .filters-grid .f-label { color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; font-size: 6px; white-space: nowrap; }
  .filters-grid .f-value { color: #0f172a; font-weight: 600; }
  .filters-grid .f-value em { color: #94a3b8; font-style: normal; font-weight: 400; }
  /* Records */
  table.records { width: 100%; border-collapse: collapse; font-size: 7px; }
  table.records thead { display: table-header-group; }
  table.records tfoot { display: table-footer-group; }
  table.records th { background: #0041C7; color: #ffffff; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; font-size: 6px; padding: 6px 4px; text-align: left; border: none; white-space: nowrap; }
  table.records td { border-bottom: 1px solid #e2e8f0; padding: 5px 4px; vertical-align: top; word-wrap: break-word; overflow-wrap: anywhere; }
  table.records tr { page-break-inside: avoid; }
  table.records tr:nth-child(even) td { background: #f8fafc; }
  .col-num { width: 22px; text-align: center; }
  .col-ref { width: 62px; }
  .badge { display: inline-block; padding: 2px 5px; border-radius: 3px; font-size: 6px; font-weight: 700; text-transform: uppercase; letter-spacing: .02em; line-height: 1; }
  .b-lost { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
  .b-found { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
  .s-pending { background: #fef3c7; color: #92400e; }
  .s-matched { background: #dbeafe; color: #1e40af; }
  .s-claimed { background: #ede9fe; color: #6d28d9; }
  .s-returned { background: #d1fae5; color: #065f46; }
  .s-archived { background: #e2e8f0; color: #475569; }
  .wrap { word-break: break-word; overflow-wrap: anywhere; }
  .item-name { font-weight: 700; color: #0f172a; font-size: 7px; }
  .item-desc { color: #64748b; font-size: 6.2px; margin-top: 1px; line-height: 1.3; }
  .empty { text-align: center; padding: 22px; border: 1.5px dashed #cbd5e1; background: #f8fafc; border-radius: 6px; font-size: 8px; color: #64748b; }
  .empty strong { color: #334155; display: block; font-size: 9px; margin-bottom: 2px; }
  /* Signatures */
  .sign-table { width: 100%; border-collapse: collapse; margin-top: 14px; }
  .sign-table td { width: 33.33%; text-align: center; vertical-align: bottom; padding: 0 12px; }
  .sign-line { border-top: 1px solid #334155; margin: 0 10px; padding-top: 4px; margin-top: 32px; }
  .sign-role { font-size: 6px; text-transform: uppercase; letter-spacing: .06em; color: #64748b; font-weight: 700; }
  .sign-name { font-size: 7.5px; font-weight: 700; color: #0f172a; margin-top: 2px; }
  .sign-date { font-size: 6px; color: #94a3b8; margin-top: 1px; }
  /* Footer */
  .footer { position: fixed; bottom: -11mm; left: 0; right: 0; text-align: center; font-size: 6px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 4px; }
  .footer .pagenum:after { content: counter(page) " of " counter(pages); }
</style>
</head>
<body>
  <div class="accent-bar"></div>

  <!-- Header -->
  <table class="header-table">
    <tr>
      <td class="logo-cell">
        @if(!empty($logoBase64))
          <img src="{{ $logoBase64 }}" alt="NAAP Logo">
        @else
          <div style="width:44px;height:44px;background:#0041C7;border-radius:50%;text-align:center;line-height:44px;color:white;font-weight:800;font-size:14px;">N</div>
        @endif
      </td>
      <td>
        <p class="org-name">National Aviation Academy of the Philippines</p>
        <p class="org-sub">Lost &amp; Found Management System &bull; Office of Student Affairs</p>
        <table class="meta-table"><tr>
          <td><strong>Generated:</strong> {{ $generatedAt->format('M d, Y h:i A') }}</td>
          <td><strong>By:</strong> {{ $generatedBy }}</td>
          <td><strong>Records:</strong> {{ $total }} item(s)</td>
          <td>
            @if($filters['date_from'] !== '—' || $filters['date_to'] !== '—')
              <strong>Period:</strong> {{ $filters['date_from'] }} &rarr; {{ $filters['date_to'] }}
            @else
              <strong>Period:</strong> All dates
            @endif
          </td>
        </tr></table>
      </td>
      <td style="width: 160px;" class="contact-box">
        <div class="brand">NAAP Lost &amp; Found</div>
        <div>Piccio Garden, Villamor, Pasay City</div>
        <div style="margin-top:3px;font-size:6px;color:#0041C7;font-weight:700;letter-spacing:.04em;">OFFICIAL REPORT</div>
      </td>
    </tr>
  </table>

  <div style="text-align:center; margin: 2px 0 8px 0;">
    <p class="title-main">Lost &amp; Found Report</p>
    <p class="title-sub">
      @if($filters['type'] !== 'All') <strong>Type:</strong> {{ ucfirst($filters['type']) }} &nbsp;&bull;&nbsp; @endif
      @if($filters['status'] !== 'All') <strong>Status:</strong> {{ ucfirst($filters['status']) }} &nbsp;&bull;&nbsp; @endif
      <strong>Category:</strong> {{ $filters['category'] }} &nbsp;&bull;&nbsp; <strong>Location:</strong> {{ $filters['location'] }}
      @if($filters['search'] !== '—') &nbsp;&bull;&nbsp; <strong>Search:</strong> "{{ \Illuminate\Support\Str::limit($filters['search'], 30) }}" @endif
    </p>
  </div>

  <!-- Summary -->
  <table class="summary-table">
    <tr>
      <td><span class="s-label">Total Records</span><span class="s-value">{{ $total }}</span><span class="s-hint">All filtered</span></td>
      <td><span class="s-label">Lost Items</span><span class="s-value" style="color:#d97706">{{ $totalLost }}</span><span class="s-hint">{{ $total ? round($totalLost/$total*100) : 0 }}% of total</span></td>
      <td><span class="s-label">Found Items</span><span class="s-value" style="color:#059669">{{ $totalFound }}</span><span class="s-hint">{{ $total ? round($totalFound/$total*100) : 0 }}% of total</span></td>
      <td><span class="s-label">Claimed / Returned</span><span class="s-value" style="color:#7c3aed">{{ $totalClaimed }}</span><span class="s-hint">Resolved</span></td>
      <td><span class="s-label">Pending / Unclaimed</span><span class="s-value" style="color:#64748b">{{ $totalUnclaimed }}</span><span class="s-hint">Awaiting action</span></td>
    </tr>
  </table>

  <!-- Filters Detail -->
  <div class="filters-box">
    <div class="filters-title"><span style="display:inline-block;width:7px;height:7px;background:#0041C7;border-radius:50%;margin-right:4px;vertical-align:middle"></span>Applied Filters</div>
    <table class="filters-grid">
      <tr>
        <td><span class="f-label">Type:</span> <span class="f-value">{{ $filters['type'] }}</span></td>
        <td><span class="f-label">Status:</span> <span class="f-value">{{ $filters['status'] }}</span></td>
        <td><span class="f-label">Category:</span> <span class="f-value">{{ $filters['category'] }}</span></td>
        <td><span class="f-label">Location:</span> <span class="f-value">{{ $filters['location'] }}</span></td>
        <td><span class="f-label">Search:</span> <span class="f-value">@if($filters['search']==='—')<em>— none —</em>@else "{{ $filters['search'] }}" @endif</span></td>
        <td><span class="f-label">Date From:</span> <span class="f-value">{{ $filters['date_from'] }}</span></td>
        <td><span class="f-label">Date To:</span> <span class="f-value">{{ $filters['date_to'] }}</span></td>
      </tr>
    </table>
  </div>

  @if($records->isEmpty())
    <div class="empty">
      <strong>No records found</strong>
      No Lost &amp; Found records matched the selected criteria. Try adjusting filters or date range.
    </div>
  @else
    <table class="records">
      <thead>
        <tr>
          <th class="col-num">#</th>
          <th class="col-ref">Ref No.</th>
          <th style="width:24%">Item</th>
          <th style="width:12%">Category</th>
          <th style="width:7%">Type</th>
          <th style="width:13%">Location</th>
          <th style="width:11%">Date Reported</th>
          <th style="width:8%">Status</th>
          <th style="width:15%">Reporter</th>
        </tr>
      </thead>
      <tbody>
        @foreach($records as $i => $r)
          <tr>
            <td class="col-num" style="text-align:center;color:#64748b">{{ $i+1 }}</td>
            <td class="col-ref" style="font-weight:700;color:#0041C7;white-space:nowrap">LF-{{ str_pad($r->id, 6, '0', STR_PAD_LEFT) }}</td>
            <td>
              <div class="item-name wrap">{{ $r->item_name ?? 'Untitled' }}</div>
              @if($r->item_description)
                <div class="item-desc wrap">{{ \Illuminate\Support\Str::limit($r->item_description, 85) }}</div>
              @endif
              @if($r->brand_model || $r->color)
                <div style="font-size:6px;color:#94a3b8;margin-top:1px" class="wrap">{{ trim(($r->brand_model ?? '').' '.($r->color ?? '')) }}</div>
              @endif
            </td>
            <td class="wrap">{{ $r->category?->name ?? '—' }}</td>
            <td><span class="badge {{ $r->report_type==='lost' ? 'b-lost' : 'b-found' }}">{{ $r->report_type }}</span></td>
            <td class="wrap">{{ $r->location?->name ?? '—' }}</td>
            <td style="white-space:nowrap">
              <div style="font-weight:600">{{ $r->incident_date ? \Carbon\Carbon::parse($r->incident_date)->format('M d, Y') : ($r->created_at?->format('M d, Y') ?? '—') }}</div>
              <div style="font-size:6px;color:#94a3b8">{{ $r->created_at?->format('h:i A') ?? '' }}</div>
            </td>
            <td><span class="badge s-{{ $r->status }}">{{ $r->status }}</span></td>
            <td class="wrap" style="font-size:6.5px"><div style="font-weight:700;color:#0f172a">{{ $r->reporter?->profile?->full_name ?? $r->reporter?->email ?? '—' }}</div><div style="color:#94a3b8">{{ $r->reporter?->email ?? '' }}</div></td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div style="margin-top:6px;text-align:right;font-size:6.5px;color:#64748b">
      Showing {{ $records->count() }} of {{ $total }} record(s) &bull; Printed {{ $generatedAt->format('M d, Y h:i A') }}
    </div>
  @endif

  <!-- Signatures -->
  <table class="sign-table">
    <tr>
      <td>
        <div class="sign-line"></div>
        <div class="sign-name">Prepared by</div>
        <div class="sign-role">OSA Staff &mdash; {{ $generatedBy }}</div>
        <div class="sign-date">Date: {{ $generatedAt->format('M d, Y') }}</div>
      </td>
      <td>
        <div class="sign-line"></div>
        <div class="sign-name">Verified by</div>
        <div class="sign-role">OSA Head / Designate</div>
        <div class="sign-date">Signature over printed name</div>
      </td>
      <td>
        <div class="sign-line"></div>
        <div class="sign-name">Noted by</div>
        <div class="sign-role">NAAP Administration</div>
        <div class="sign-date">Signature over printed name</div>
      </td>
    </tr>
  </table>

  <div class="footer">
    <div>National Aviation Academy of the Philippines — Lost &amp; Found Management System &nbsp;|&nbsp; Confidential — For official use only &nbsp;|&nbsp; Generated {{ $generatedAt->format('M d, Y h:i A') }} &nbsp;|&nbsp; Page <span class="pagenum"></span></div>
    <div style="margin-top:1px">Piccio Garden, Villamor, Pasay City &bull; This is a system-generated report. No erasures allowed.</div>
  </div>
</body>
</html>
