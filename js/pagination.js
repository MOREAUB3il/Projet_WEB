// ========================================
// SYSTÈME DE PAGINATION AMÉLIORÉ
// ========================================

class PaginationManager {
    constructor(containerId, itemsPerPage = 10) {
        this.container = document.getElementById(containerId);
        this.itemsPerPage = itemsPerPage;
        this.currentPage = 1;
        this.items = [];
        this.filteredItems = [];
    }

    loadItems(items) {
        this.items = items;
        this.filteredItems = items;
        this.currentPage = 1;
        this.render();
    }

    filterItems(searchQuery) {
        if (!searchQuery) {
            this.filteredItems = this.items;
        } else {
            const query = searchQuery.toLowerCase();
            this.filteredItems = this.items.filter(item =>
                item.description && item.description.toLowerCase().includes(query) ||
                item.author && item.author.toLowerCase().includes(query)
            );
        }
        this.currentPage = 1;
        this.render();
    }

    render() {
        if (!this.container) return;

        // Calculer la pagination
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        const pageItems = this.filteredItems.slice(start, end);

        // Effacer le container
        this.container.innerHTML = '';

        // Afficher les items de la page
        if (pageItems.length === 0) {
            this.container.innerHTML = '<p style="text-align: center; color: #999;">Aucun résultat trouvé</p>';
            return;
        }

        pageItems.forEach(item => {
            const itemEl = document.createElement('li');
            itemEl.innerHTML = `
                <a href="view_capsule.php?id=${item.id}">
                    <img src="${item.image}" alt="${item.description}" class="menu-icon lazy" loading="lazy">
                    <p>${item.description.substring(0, 50)}...</p>
                    <p style="font-size: 12px; color: #999;">Par ${item.author}</p>
                </a>
                <div class="like">
                    <button class="heart-container ${item.is_liked ? 'is-active' : ''}" onclick="toggleHeart(this, ${item.id})">
                        <svg class="heart-icon" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"></path>
                        </svg>
                    </button>
                    <button class="bookmark-btn ${item.is_liked ? 'is-active' : ''}" onclick="toggleBookmark(this, ${item.id})">
                        <svg class="bookmark-icon" viewBox="0 0 24 24">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </button>
                </div>
            `;
            this.container.appendChild(itemEl);
        });

        // Afficher la pagination si nécessaire
        const totalPages = Math.ceil(this.filteredItems.length / this.itemsPerPage);
        if (totalPages > 1) {
            this.renderPagination(totalPages);
        }
    }

    renderPagination(totalPages) {
        const paginationDiv = document.createElement('div');
        paginationDiv.className = 'pagination-container';
        paginationDiv.style.marginTop = '30px';
        paginationDiv.style.textAlign = 'center';

        const ul = document.createElement('ul');
        ul.className = 'pagination';

        // Bouton précédent
        if (this.currentPage > 1) {
            const prev = document.createElement('li');
            prev.innerHTML = `<a href="#" onclick="paginationManager.goToPage(${this.currentPage - 1}); return false;">← Précédent</a>`;
            ul.appendChild(prev);
        }

        // Pages numérotées
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            if (i === this.currentPage) {
                li.innerHTML = `<span class="active">${i}</span>`;
            } else if (i <= 3 || i >= totalPages - 2 || (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
                li.innerHTML = `<a href="#" onclick="paginationManager.goToPage(${i}); return false;">${i}</a>`;
            } else if (i === 4 || i === totalPages - 3) {
                li.innerHTML = '<span>...</span>';
            }
            ul.appendChild(li);
        }

        // Bouton suivant
        if (this.currentPage < totalPages) {
            const next = document.createElement('li');
            next.innerHTML = `<a href="#" onclick="paginationManager.goToPage(${this.currentPage + 1}); return false;">Suivant →</a>`;
            ul.appendChild(next);
        }

        paginationDiv.appendChild(ul);
        this.container.parentElement.insertBefore(paginationDiv, this.container.nextSibling);
    }

    goToPage(page) {
        const totalPages = Math.ceil(this.filteredItems.length / this.itemsPerPage);
        if (page >= 1 && page <= totalPages) {
            this.currentPage = page;
            this.render();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
}

// ========================================
// RECHERCHE ET FILTRAGE
// ========================================

let paginationManager;

function initPagination() {
    paginationManager = new PaginationManager('BLOC', 12);
}

function setupSearchbar() {
    const searchbar = document.querySelector('.search-input');
    if (searchbar) {
        searchbar.addEventListener('input', (e) => {
            if (paginationManager) {
                paginationManager.filterItems(e.target.value);
            }
        });
    }
}

// ========================================
// LAZY LOADING IMAGES
// ========================================

function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img.lazy').forEach(img => imageObserver.observe(img));
    } else {
        // Fallback pour les navigateurs sans IntersectionObserver
        document.querySelectorAll('img.lazy').forEach(img => {
            img.src = img.dataset.src || img.src;
            img.classList.remove('lazy');
            img.classList.add('loaded');
        });
    }
}

// ========================================
// Toast Notifications Améliorées
// ========================================

function showToast(message, type = 'success', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// ========================================
// Initialisation au chargement
// ========================================

document.addEventListener('DOMContentLoaded', () => {
    initPagination();
    setupSearchbar();
    initLazyLoading();
});
