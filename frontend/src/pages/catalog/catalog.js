import { initMobileNav } from '../../components/layout/mobileNav.js';

document.addEventListener('DOMContentLoaded', function () {
    initMobileNav();

    const filterTabs = document.querySelectorAll('.filter-tab');
    const catalogCards = document.querySelectorAll('.catalog-card');
    const oreSection = document.getElementById('ore-section');
    const liquidSection = document.getElementById('liquid-section');

    filterTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            filterTabs.forEach(function (t) {
                t.classList.remove('active');
            });
            this.classList.add('active');

            const filter = this.dataset.filter;

            if (filter === 'all') {
                oreSection.style.display = 'block';
                liquidSection.style.display = 'block';
                catalogCards.forEach(function (card) {
                    card.style.display = 'block';
                });
            } else if (filter === 'ore') {
                oreSection.style.display = 'block';
                liquidSection.style.display = 'none';
            } else if (filter === 'liquid') {
                oreSection.style.display = 'none';
                liquidSection.style.display = 'block';
            }
        });
    });

    const searchInput = document.getElementById('catalogSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();

            catalogCards.forEach(function (card) {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
