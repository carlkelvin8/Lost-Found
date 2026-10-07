@extends('layouts.app')

@section('title', 'Calendar · NAAP Lost & Found')

@push('styles')
<link href="{{ asset('css/calendar.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="calendar-page-header">
  <div>
    <h1><i class="bi bi-calendar3"></i> Calendar</h1>
    <p class="calendar-subtitle">View Lost & Found activity by date — counts update automatically from your existing reports.</p>
  </div>
  <a href="{{ route('reports.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Report</a>
</div>

{{-- Toolbar: Month navigation + Filters --}}
<div class="calendar-toolbar">
  <div class="calendar-nav">
    <button id="calPrev" class="btn btn-outline-secondary btn-sm" aria-label="Previous month"><i class="bi bi-chevron-left"></i></button>
    <div id="calTitle" class="calendar-nav-title">—</div>
    <button id="calNext" class="btn btn-outline-secondary btn-sm" aria-label="Next month"><i class="bi bi-chevron-right"></i></button>
    <button id="calToday" class="btn btn-sm btn-outline-primary">Today</button>
  </div>
  <div class="calendar-filters-bar">
    <select id="calType" class="form-select">
      <option value="">All Types</option>
      <option value="lost">Lost</option>
      <option value="found">Found</option>
    </select>
    <select id="calCategory" class="form-select">
      <option value="">All Categories</option>
      @foreach($categories as $c)
        <option value="{{ $c->id }}">{{ $c->name }}</option>
      @endforeach
    </select>
    <select id="calStatus" class="form-select">
      <option value="">All Statuses</option>
      @foreach($statuses as $s)
        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
      @endforeach
    </select>
  </div>
</div>

<div class="calendar-legend">
  <span class="legend-badge legend-lost"><i class="bi bi-exclamation-circle"></i> Lost</span>
  <span class="legend-badge legend-found"><i class="bi bi-check-circle"></i> Found</span>
  <span class="legend-badge legend-total"><i class="bi bi-collection"></i> Total</span>
</div>

<div class="calendar-card">
  <div class="calendar-weekdays">
    <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
  </div>
  <div id="calendarLoading" class="calendar-loading" style="display:none">
    <span class="spinner-sm"></span> Loading calendar…
  </div>
  <div id="calendarGrid" class="calendar-grid"></div>
</div>

{{-- Day detail panel (hidden until a date is selected) --}}
<div id="dayPanel" class="day-panel" style="display:none">
  <div class="day-panel-header">
    <div id="dayPanelTitle" class="day-panel-title">—</div>
    <div id="dayPanelCounts" class="day-panel-counts"></div>
    <button id="dayPanelClose" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i> Close</button>
  </div>
  <div id="dayPanelLoading" class="calendar-loading" style="display:none"><span class="spinner-sm"></span> Loading reports…</div>
  <div id="dayPanelBody" class="day-panel-body">
    <div>
      <div class="day-col-title lost"><i class="bi bi-exclamation-circle-fill"></i> Lost Items — <span id="lostCount">0</span></div>
      <div id="lostList" class="day-items"></div>
    </div>
    <div>
      <div class="day-col-title found"><i class="bi bi-check-circle-fill"></i> Found Items — <span id="foundCount">0</span></div>
      <div id="foundList" class="day-items"></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const grid = document.getElementById('calendarGrid');
  const title = document.getElementById('calTitle');
  const loading = document.getElementById('calendarLoading');
  const btnPrev = document.getElementById('calPrev');
  const btnNext = document.getElementById('calNext');
  const btnToday = document.getElementById('calToday');
  const selType = document.getElementById('calType');
  const selCategory = document.getElementById('calCategory');
  const selStatus = document.getElementById('calStatus');

  const dayPanel = document.getElementById('dayPanel');
  const dayPanelTitle = document.getElementById('dayPanelTitle');
  const dayPanelCounts = document.getElementById('dayPanelCounts');
  const dayPanelBody = document.getElementById('dayPanelBody');
  const dayPanelLoading = document.getElementById('dayPanelLoading');
  const lostList = document.getElementById('lostList');
  const foundList = document.getElementById('foundList');
  const lostCountEl = document.getElementById('lostCount');
  const foundCountEl = document.getElementById('foundCount');
  const dayPanelClose = document.getElementById('dayPanelClose');

  let current = new Date();
  current.setDate(1);
  let countsMap = {}; // dateStr -> {lost, found, total}
  let selectedDate = null;

  function formatMonth(d){
    return d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
  }
  function pad(n){ return n < 10 ? '0'+n : ''+n; }
  function toISO(date){ return date.getFullYear()+'-'+pad(date.getMonth()+1)+'-'+pad(date.getDate()); }

  function getFilters(){
    const p = new URLSearchParams();
    if (selType.value) p.set('type', selType.value);
    if (selCategory.value) p.set('category_id', selCategory.value);
    if (selStatus.value) p.set('status', selStatus.value);
    return p;
  }

  function getGridRange(year, month){
    // month 0-indexed
    const first = new Date(year, month, 1);
    const last = new Date(year, month+1, 0);
    const startDay = first.getDay(); // 0 Sun
    const gridStart = new Date(first);
    gridStart.setDate(first.getDate() - startDay);
    const endDay = last.getDay();
    const gridEnd = new Date(last);
    gridEnd.setDate(last.getDate() + (6 - endDay));
    return { gridStart, gridEnd, first, last };
  }

  async function fetchCounts(){
    const year = current.getFullYear();
    const month = current.getMonth();
    const { gridStart, gridEnd } = getGridRange(year, month);
    const params = getFilters();
    params.set('start', toISO(gridStart));
    params.set('end', toISO(gridEnd));
    // also keep year/month for fallback
    params.set('year', year);
    params.set('month', month+1);

    loading.style.display = 'flex';
    try {
      const res = await fetch("{{ route('calendar.counts') }}?"+params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      if (!res.ok) throw new Error('Failed to fetch counts');
      const data = await res.json();
      countsMap = data.counts || {};
    } catch(e){
      console.error(e);
      countsMap = {};
    } finally {
      loading.style.display = 'none';
    }
  }

  function renderGrid(){
    title.textContent = formatMonth(current);
    const year = current.getFullYear();
    const month = current.getMonth();
    const { gridStart, gridEnd } = getGridRange(year, month);
    const todayStr = toISO(new Date());

    grid.innerHTML = '';
    let cursor = new Date(gridStart);
    while (cursor <= gridEnd){
      const iso = toISO(cursor);
      const isCurrentMonth = cursor.getMonth() === month;
      const isToday = iso === todayStr;
      const isSelected = selectedDate === iso;
      const entry = countsMap[iso] || null;
      const hasReports = entry && entry.total > 0;

      const cell = document.createElement('div');
      cell.className = 'cal-cell'
        + (isCurrentMonth ? '' : ' is-other-month')
        + (isToday ? ' is-today' : '')
        + (isSelected ? ' is-selected' : '')
        + (hasReports ? ' has-reports' : '')
        + (hasReports && entry.lost>0 && entry.found>0 ? ' is-mixed' : '')
        + (hasReports && entry.lost>0 && entry.found===0 ? ' is-lost-only' : '')
        + (hasReports && entry.found>0 && entry.lost===0 ? ' is-found-only' : '');
      cell.setAttribute('role','button');
      cell.setAttribute('tabindex','0');
      cell.setAttribute('aria-label', iso);
      cell.dataset.date = iso;

      const num = document.createElement('div');
      num.className = 'cal-day-num';
      num.textContent = cursor.getDate();
      cell.appendChild(num);

      if (hasReports){
        const badges = document.createElement('div');
        badges.className = 'cal-badges';
        if (entry.lost > 0){
          const b = document.createElement('span');
          b.className = 'cal-badge cal-badge-lost';
          b.setAttribute('data-short', 'L:'+entry.lost);
          b.innerHTML = '<span>Lost: '+entry.lost+'</span>';
          badges.appendChild(b);
        }
        if (entry.found > 0){
          const b = document.createElement('span');
          b.className = 'cal-badge cal-badge-found';
          b.setAttribute('data-short', 'F:'+entry.found);
          b.innerHTML = '<span>Found: '+entry.found+'</span>';
          badges.appendChild(b);
        }
        // total badge only if both present or to show total when filtered
        const t = document.createElement('span');
        t.className = 'cal-badge cal-badge-total';
        t.setAttribute('data-short', 'T:'+entry.total);
        t.innerHTML = '<span>Total: '+entry.total+'</span>';
        badges.appendChild(t);
        cell.appendChild(badges);
      }

      cell.addEventListener('click', ()=> selectDate(iso));
      cell.addEventListener('keydown', (e)=>{
        if (e.key==='Enter' || e.key===' ') { e.preventDefault(); selectDate(iso); }
      });

      grid.appendChild(cell);
      cursor.setDate(cursor.getDate()+1);
    }
  }

  async function refresh(){
    await fetchCounts();
    renderGrid();
  }

  async function selectDate(iso){
    selectedDate = iso;
    renderGrid();
    // show panel
    dayPanel.style.display = 'block';
    const d = new Date(iso+'T00:00:00');
    dayPanelTitle.textContent = d.toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
    dayPanelCounts.innerHTML = '';
    dayPanelLoading.style.display = 'flex';
    dayPanelBody.style.display = 'none';
    lostList.innerHTML = '';
    foundList.innerHTML = '';

    const params = getFilters();
    params.set('date', iso);
    try{
      const res = await fetch("{{ route('calendar.day') }}?"+params.toString(), {
        headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' }
      });
      if (!res.ok) throw new Error('Failed to fetch day');
      const data = await res.json();
      lostCountEl.textContent = data.lost_count;
      foundCountEl.textContent = data.found_count;

      dayPanelCounts.innerHTML = ''
        + (data.lost_count>0 ? '<span class="legend-badge legend-lost">Lost: '+data.lost_count+'</span>' : '')
        + (data.found_count>0 ? '<span class="legend-badge legend-found">Found: '+data.found_count+'</span>' : '')
        + '<span class="legend-badge legend-total">Total: '+data.total+'</span>';

      renderItems(lostList, data.lost_items, 'lost');
      renderItems(foundList, data.found_items, 'found');

      // Scroll to panel on mobile
      if (window.innerWidth < 992){
        dayPanel.scrollIntoView({ behavior:'smooth', block:'start' });
      }
    } catch(e){
      console.error(e);
      lostList.innerHTML = '<div class="day-empty">Failed to load reports</div>';
      foundList.innerHTML = '<div class="day-empty">Failed to load reports</div>';
    } finally {
      dayPanelLoading.style.display = 'none';
      dayPanelBody.style.display = 'grid';
    }
  }

  function renderItems(container, items, type){
    container.innerHTML = '';
    if (!items || items.length===0){
      container.innerHTML = '<div class="day-empty">No '+(type==='lost'?'lost':'found')+' items for this date</div>';
      return;
    }
    items.forEach(it=>{
      const a = document.createElement('a');
      a.href = it.detail_url;
      a.className = 'day-item';
      const thumb = document.createElement('div');
      thumb.className = 'day-item-thumb';
      if (it.photo_url){
        const img = document.createElement('img');
        img.src = it.photo_url;
        img.alt = it.item_name || 'Item';
        img.loading = 'lazy';
        thumb.appendChild(img);
      } else {
        thumb.innerHTML = '<i class="bi bi-image" style="font-size:1.5rem"></i>';
      }
      const main = document.createElement('div');
      main.className = 'day-item-main';
      const name = document.createElement('div');
      name.className = 'day-item-name';
      name.textContent = it.item_name || 'Untitled Item';
      const desc = document.createElement('div');
      desc.className = 'day-item-desc';
      desc.textContent = it.item_description || '';
      const meta = document.createElement('div');
      meta.className = 'day-item-meta';
      if (it.category) meta.innerHTML += '<span><i class="bi bi-tag"></i> '+escapeHtml(it.category)+'</span>';
      if (it.location) meta.innerHTML += '<span><i class="bi bi-geo-alt"></i> '+escapeHtml(it.location)+'</span>';
      if (it.incident_date) meta.innerHTML += '<span><i class="bi bi-calendar"></i> '+escapeHtml(it.incident_date)+'</span>';
      if (it.reporter) meta.innerHTML += '<span><i class="bi bi-person"></i> '+escapeHtml(it.reporter)+'</span>';
      const status = document.createElement('span');
      status.className = 'status-chip '+(it.status||'pending');
      status.textContent = it.status || 'pending';
      meta.appendChild(status);

      main.appendChild(name);
      main.appendChild(desc);
      main.appendChild(meta);
      a.appendChild(thumb);
      a.appendChild(main);
      container.appendChild(a);
    });
  }

  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m]));
  }

  // Events
  btnPrev.addEventListener('click', ()=>{ current.setMonth(current.getMonth()-1); refresh(); });
  btnNext.addEventListener('click', ()=>{ current.setMonth(current.getMonth()+1); refresh(); });
  btnToday.addEventListener('click', ()=>{ current = new Date(); current.setDate(1); selectedDate = toISO(new Date()); refresh().then(()=> selectDate(selectedDate)); });
  [selType, selCategory, selStatus].forEach(el=> el.addEventListener('change', ()=>{ refresh(); if (selectedDate) selectDate(selectedDate); }));
  dayPanelClose.addEventListener('click', ()=>{ dayPanel.style.display='none'; selectedDate=null; renderGrid(); });

  // Initial load
  refresh();
})();
</script>
@endpush
