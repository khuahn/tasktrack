// Head Navigation JavaScript - Safe and Isolated
(function() {
    'use strict';
    
    class Navigation {
        constructor() {
            this.burgerBtn = document.getElementById('burgerBtn');
            this.closeBtn = document.getElementById('closeBtn');
            this.navMenu = document.getElementById('navMenu');
            this.overlay = document.getElementById('overlay');
            this.filterModal = document.getElementById('globalFilterModal');
            
            if (this.burgerBtn && this.navMenu) {
                this.init();
            }
        }
        
        init() {
            this.bindEvents();
        }
        
        bindEvents() {
            // Open menu
            if (this.burgerBtn) this.burgerBtn.addEventListener('click', () => this.openMenu());
            
            // Close menu
            if (this.closeBtn) this.closeBtn.addEventListener('click', () => this.closeMenu());
            if (this.overlay) this.overlay.addEventListener('click', () => this.closeMenu());
            
            // Close menu with Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeMenu();
                }
            });
            
            // Close menu when clicking on menu links
            document.querySelectorAll('.menu-link').forEach(link => {
                link.addEventListener('click', () => this.closeMenu());
            });
        }
        
        openMenu() {
            this.navMenu.classList.add('active');
            this.overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        closeMenu() {
            this.navMenu.classList.remove('active');
            this.overlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    }
    
    // Initialize navigation when DOM is loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new Navigation();
            initGlobalFilters();
        });
    } else {
        new Navigation();
        initGlobalFilters();
    }

    function initGlobalFilters() {
        window.openFilterModal = function() {
            const modal = document.getElementById('globalFilterModal');
            if (modal) modal.style.display = 'flex';
        };
        window.closeFilterModal = function() {
            const modal = document.getElementById('globalFilterModal');
            if (modal) modal.style.display = 'none';
        };
        window.applyGlobalFilters = function() {
            const p = document.getElementById('gf_priority')?.value || '';
            const a = document.getElementById('gf_assignee')?.value || '';
            const q = document.getElementById('gf_query')?.value || '';
            const params = new URLSearchParams(window.location.search);
            if (p !== undefined) params.set('f_priority', p);
            if (a !== undefined && a !== '') params.set('f_assigned_to', a); else params.delete('f_assigned_to');
            if (q !== undefined) params.set('f_q', q);
            window.location.search = params.toString();
            window.closeFilterModal();
        };

        // Add Task link scrolls to Add Task section inside the modal
        window.openAddTaskModal = function() {
            window.openFilterModal();
            setTimeout(() => {
                const header = document.getElementById('addTaskSection');
                if (header) header.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 50);
        };
    }
})();
