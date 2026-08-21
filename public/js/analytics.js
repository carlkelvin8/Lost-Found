/**
 * Analytics Dashboard Features
 * Recovery rate, avg match time, peak hours chart, location heatmap
 */

// ==================== RECOVERY RATE GAUGE ====================
function createRecoveryGauge(rate, size = 120) {
    const radius = (size - 16) / 2;
    const circumference = 2 * Math.PI * radius;
    const offset = circumference - (rate / 100) * circumference;
    
    const color = rate >= 70 ? '#10b981' : rate >= 40 ? '#f59e0b' : '#ef4444';
    
    return `
        <div style="text-align:center">
            <svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
                <circle cx="${size/2}" cy="${size/2}" r="${radius}" fill="none" stroke="#e2e8f0" stroke-width="12"/>
                <circle cx="${size/2}" cy="${size/2}" r="${radius}" fill="none" stroke="${color}" stroke-width="12"
                        stroke-dasharray="${circumference}" stroke-dashoffset="${offset}"
                        stroke-linecap="round" transform="rotate(-90 ${size/2} ${size/2})"
                        style="transition: stroke-dashoffset 1s ease-out"/>
                <text x="${size/2}" y="${size/2 - 8}" text-anchor="middle" font-size="24" font-weight="700" fill="${color}">${rate}%</text>
                <text x="${size/2}" y="${size/2 + 12}" text-anchor="middle" font-size="11" fill="#94a3b8">Recovery</text>
            </svg>
        </div>
    `;
}

// ==================== PEAK HOURS CHART ====================
function createPeakHoursChart(data, width = 400, height = 200) {
    const maxVal = Math.max(...data.map(d => d.count), 1);
    const barWidth = (width - 40) / data.length;
    const chartHeight = height - 40;
    
    let bars = '';
    data.forEach((d, i) => {
        const barHeight = (d.count / maxVal) * chartHeight;
        const x = 30 + i * barWidth;
        const y = chartHeight - barHeight + 10;
        const isPeak = d.count === maxVal;
        
        bars += `
            <rect x="${x}" y="${y}" width="${barWidth - 4}" height="${barHeight}" 
                  rx="4" fill="${isPeak ? '#0041C7' : '#e2e8f0'}" 
                  style="transition: height 0.5s ease"/>
            <text x="${x + (barWidth - 4) / 2}" y="${height - 5}" text-anchor="middle" 
                  font-size="9" fill="#94a3b8">${d.hour}</text>
            ${d.count > 0 ? `<text x="${x + (barWidth - 4) / 2}" y="${y - 5}" text-anchor="middle" font-size="10" fill="#64748b" font-weight="600">${d.count}</text>` : ''}
        `;
    });
    
    return `
        <svg width="100%" height="${height}" viewBox="0 0 ${width} ${height}">
            <line x1="30" y1="10" x2="30" y2="${chartHeight + 10}" stroke="#e2e8f0" stroke-width="1"/>
            <line x1="30" y1="${chartHeight + 10}" x2="${width}" y2="${chartHeight + 10}" stroke="#e2e8f0" stroke-width="1"/>
            ${bars}
        </svg>
    `;
}

// ==================== LOCATION HEATMAP ====================
function createLocationHeatmap(locations, width = 300, height = 200) {
    if (!locations || !locations.length) {
        return '<div style="text-align:center;padding:2rem;color:#94a3b8">No location data</div>';
    }
    
    const maxCount = Math.max(...locations.map(l => l.count), 1);
    
    const items = locations.map((loc, i) => {
        const intensity = loc.count / maxCount;
        const size = 40 + intensity * 40;
        const x = 20 + (i % 5) * (width / 5);
        const y = 20 + Math.floor(i / 5) * (height / 3);
        
        return `
            <g transform="translate(${x}, ${y})">
                <circle r="${size/2}" fill="rgba(0,65,199,${0.1 + intensity * 0.4})" 
                        stroke="rgba(0,65,199,${0.2 + intensity * 0.3})" stroke-width="2"/>
                <text text-anchor="middle" y="3" font-size="10" fill="#1e293b" font-weight="600">${loc.count}</text>
                <text text-anchor="middle" y="16" font-size="8" fill="#64748b">${loc.name.substring(0, 12)}</text>
            </g>
        `;
    }).join('');
    
    return `
        <svg width="100%" height="${height}" viewBox="0 0 ${width} ${height}">
            ${items}
        </svg>
    `;
}

// ==================== AVERAGE MATCH TIME ====================
function formatMatchTime(hours) {
    if (hours < 1) return `${Math.round(hours * 60)}m`;
    if (hours < 24) return `${Math.round(hours)}h`;
    return `${Math.round(hours / 24)}d`;
}

function createMatchTimeDisplay(avgHours) {
    const color = avgHours <= 24 ? '#10b981' : avgHours <= 72 ? '#f59e0b' : '#ef4444';
    const label = avgHours <= 24 ? 'Fast' : avgHours <= 72 ? 'Average' : 'Slow';
    
    return `
        <div style="text-align:center;padding:1rem">
            <div style="font-size:2rem;font-weight:700;color:${color}">${formatMatchTime(avgHours)}</div>
            <div style="font-size:0.8125rem;color:#94a3b8;margin-top:0.25rem">Avg Match Time</div>
            <div style="font-size:0.75rem;color:${color};font-weight:600;margin-top:0.5rem">
                <i class="bi bi-${avgHours <= 24 ? 'lightning-fill' : avgHours <= 72 ? 'clock' : 'hourglass'}"></i> ${label}
            </div>
        </div>
    `;
}

// ==================== STAT CARDS WITH SPARKLINES ====================
function createStatWithSparkline(label, value, trend, data, color = '#0041C7') {
    const sparkline = createMiniSparkline(data, color);
    
    return `
        <div style="background:white;border:1px solid #e2e8f0;border-radius:14px;padding:1.25rem">
            <div style="display:flex;justify-content:space-between;align-items:flex-start">
                <div>
                    <div style="font-size:0.8125rem;color:#94a3b8;font-weight:500">${label}</div>
                    <div style="font-size:1.75rem;font-weight:700;color:#0f172a;margin-top:0.25rem">${value}</div>
                </div>
                ${trend !== undefined ? `
                    <div style="font-size:0.75rem;font-weight:600;color:${trend >= 0 ? '#10b981' : '#ef4444'};display:flex;align-items:center;gap:0.25rem">
                        <i class="bi bi-arrow-${trend >= 0 ? 'up' : 'down'}"></i>
                        ${Math.abs(trend)}%
                    </div>
                ` : ''}
            </div>
            <div style="margin-top:0.75rem">${sparkline}</div>
        </div>
    `;
}

function createMiniSparkline(data, color = '#0041C7') {
    if (!data || !data.length) return '';
    
    const width = 120;
    const height = 30;
    const max = Math.max(...data, 1);
    const min = Math.min(...data, 0);
    const range = max - min || 1;
    
    const points = data.map((val, i) => {
        const x = (i / (data.length - 1)) * width;
        const y = height - ((val - min) / range) * height;
        return `${x},${y}`;
    }).join(' ');
    
    const areaPoints = `0,${height} ${points} ${width},${height}`;
    
    return `
        <svg width="100%" height="${height}" viewBox="0 0 ${width} ${height}" preserveAspectRatio="none">
            <defs>
                <linearGradient id="sparkline-${color.replace('#', '')}" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color:${color};stop-opacity:0.2"/>
                    <stop offset="100%" style="stop-color:${color};stop-opacity:0"/>
                </linearGradient>
            </defs>
            <polygon points="${areaPoints}" fill="url(#sparkline-${color.replace('#', '')})"/>
            <polyline points="${points}" fill="none" stroke="${color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    `;
}
