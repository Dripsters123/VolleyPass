function toJsonOrError(res) {
    return res.text().then(t => {
        try { return JSON.parse(t); } 
        catch { throw new Error(`Invalid JSON: ${t.slice(0, 300)}...`); }
    });
}

function imgUrl(hash) {
    return hash ? `https://images.sportdevs.com/${hash}.png` : '';
}

function renderMatch(m) {
    const homeImg = imgUrl(m.home_team_hash_image);
    const awayImg = imgUrl(m.away_team_hash_image);

    return `
        <li class="bg-white p-4 rounded shadow">
            <div class="flex items-center gap-3 flex-wrap">
                ${homeImg ? `<img src="${homeImg}" class="h-6 w-6" />` : ''}
                <strong>${m.home_team_name}</strong>
                <span class="text-gray-500">vs</span>
                <strong>${m.away_team_name}</strong>
                ${awayImg ? `<img src="${awayImg}" class="h-6 w-6" />` : ''}
            </div>

            <div class="text-sm text-gray-500 mt-1">
                ${m.tournament_name ?? 'Tournament'} • ${m.start_time ? new Date(m.start_time).toLocaleString() : ''}
            </div>

            <a href="/volleyball/match/${m.id}" class="mt-3 inline-block px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                More Info
            </a>
        </li>
    `;
}

function loadMatches(tournament = '', date = '', home = '', away = '') {
    const container = document.getElementById('upcoming-matches');
    if (!container) return;

    const params = new URLSearchParams();
    if (tournament) params.append('tournament', tournament);
    if (date) params.append('date', date);
    if (home) params.append('home_team', home);
    if (away) params.append('away_team', away);

    fetch(`/api/volleyball/upcoming?${params.toString()}`, { headers: { 'Accept': 'application/json' } })
        .then(toJsonOrError)
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = '<div class="bg-white p-4 rounded shadow">No matches found for this filter.</div>';
                return;
            }
            const html = data.map(renderMatch).join('');
            container.innerHTML = `<ul class="space-y-4">${html}</ul>`;
        })
        .catch(err => {
            console.error('Upcoming matches error:', err);
            container.innerHTML = '<div class="bg-white p-4 rounded shadow">Failed to load matches.</div>';
        });
}

document.addEventListener('DOMContentLoaded', () => {
    loadMatches(); // initial load

    document.getElementById('filter-button')?.addEventListener('click', () => {
        const tournament = document.getElementById('tournament')?.value || '';
        const date = document.getElementById('date')?.value || '';
        const home = document.getElementById('home_team')?.value || '';
        const away = document.getElementById('away_team')?.value || '';
        loadMatches(tournament, date, home, away);
    });
});
