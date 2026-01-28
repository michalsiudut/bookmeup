document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('dashboard-search');
    const specialistsGrid = document.getElementById('specialists-grid');
    let searchTimeout;

    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            handleSearch();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(handleSearch, 300);
        });
    }

    async function handleSearch() {
        const query = searchInput.value;

        // Update URL without refresh
        const url = new URL(window.location);
        if (query) {
            url.searchParams.set('search', query);
        } else {
            url.searchParams.delete('search');
        }
        window.history.pushState({}, '', url);

        try {
            const response = await fetch(`/searchBusinesses?search=${encodeURIComponent(query)}`);
            const data = await response.json();
            renderBusinesses(data, !!query);
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    function renderBusinesses(businesses, isSearching) {
        specialistsGrid.innerHTML = '';

        if (isSearching) {
            specialistsGrid.classList.add('list-view-mode');
        } else {
            specialistsGrid.classList.remove('list-view-mode');
        }

        if (businesses.length === 0) {
            specialistsGrid.innerHTML = '<p>Obecnie nie znaleziono żadnych specjalistów pasujących do Twoich kryteriów.</p>';
            return;
        }

        businesses.forEach(business => {
            const card = document.createElement('div');
            card.className = 'specialist-card';

            const ratingWidth = (business.rating || 0) * 20;
            const reviewCount = business.review_count || 0;
            const imageUrl = business.image_url || '';

            card.innerHTML = `
                <div class="card-img" style="background-image: url('${imageUrl}');"></div>
                <div class="card-content">
                    <h3>${business.name}</h3>
                    <p class="category">${business.category} • ${business.city}</p>
                    <div class="rating">
                        <div class="stars-container">
                            <div class="stars-outer"></div>
                            <div class="stars-inner" style="width: ${ratingWidth}%;"></div>
                        </div>
                        <span class="count">(${reviewCount})</span>
                    </div>
                    <form action="/business_profile" method="GET">
                        <input type="hidden" name="id" value="${business.id}">
                        <button type="submit" class="select-btn">Wybierz</button>
                    </form>
                </div>
            `;
            specialistsGrid.appendChild(card);
        });
    }
});
