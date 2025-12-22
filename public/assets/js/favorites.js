document.addEventListener('DOMContentLoaded', () => {

    const STORAGE_KEY = 'studienkolleg_favorites';

    /* ===============================
       Helpers
    =============================== */
    const getFavorites = () => {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
        } catch (e) {
            return [];
        }
    };

    const saveFavorites = (favorites) => {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(favorites));
    };

    /* ===============================
       Init favorites state
    =============================== */
    const initFavorites = () => {
        const favorites = getFavorites();

        document.querySelectorAll('.favorite-btn').forEach(btn => {
            const id = btn.dataset.id;
            if (favorites.includes(id)) {
                btn.classList.add('active');
            }
        });
    };

    /* ===============================
       Toggle favorite
    =============================== */
    const bindFavoriteEvents = () => {
        document.querySelectorAll('.favorite-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();

                const id = this.dataset.id;
                let favorites = getFavorites();

                if (favorites.includes(id)) {
                    favorites = favorites.filter(item => item !== id);
                    this.classList.remove('active');
                } else {
                    favorites.push(id);
                    this.classList.add('active');
                }

                saveFavorites(favorites);
            });
        });
    };

    /* ===============================
       Init
    =============================== */
    initFavorites();
    bindFavoriteEvents();

});
