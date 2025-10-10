// Head Navigation JavaScript - Safe and Isolated
(function() {
    'use strict';
    
    class Navigation {
        constructor() {
            this.burgerBtn = document.getElementById('burgerBtn');
            this.closeBtn = document.getElementById('closeBtn');
            this.navMenu = document.getElementById('navMenu');
            this.overlay = document.getElementById('overlay');
            
            if (this.burgerBtn && this.navMenu) {
                this.init();
            }
        }
        
        init() {
            this.bindEvents();
        }
        
        bindEvents() {
            // Open menu
            this.burgerBtn.addEventListener('click', () => this.openMenu());
            
            // Close menu
            this.closeBtn.addEventListener('click', () => this.closeMenu());
            this.overlay.addEventListener('click', () => this.closeMenu());
            
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
        });
    } else {
        new Navigation();
    }
})();
