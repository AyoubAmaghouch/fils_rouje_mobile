/**
 * Admin Panel — JavaScript Complet (Tailwind CSS Edition)
 * Module UI Feedback : Loading Spinners, Toasts, Validation Formulaire & API
 */

(function() {
    'use strict';

    // =========================================================
    // 1. GESTIONNAIRE DE TOAST NOTIFICATIONS (Req B)
    // =========================================================

    /**
     * Crée ou récupère le conteneur global des toasts
     */
    function getToastContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-5 right-5 z-[9999] flex flex-col gap-3 max-w-sm w-full pointer-events-none px-4 sm:px-0';
            document.body.appendChild(container);
        }
        return container;
    }

    /**
     * Affiche une notification Toast stylisée
     * @param {string} message - Message à afficher
     * @param {'success'|'error'|'danger'|'warning'|'info'} [type='info'] - Type de notification
     * @param {number} [duration=4000] - Durée en millisecondes avant disparition
     */
    function showToast(message, type = 'info', duration = 4000) {
        if (!message) return;

        // Normalisation type (danger -> error)
        const normalizedType = type === 'danger' ? 'error' : type;

        const config = {
            success: {
                icon: `<svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
                border: 'border-emerald-500/30',
                bg: 'bg-[#18182e]/95',
                badgeBg: 'bg-emerald-500/10',
                title: 'Succès'
            },
            error: {
                icon: `<svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
                border: 'border-red-500/30',
                bg: 'bg-[#18182e]/95',
                badgeBg: 'bg-red-500/10',
                title: 'Erreur'
            },
            warning: {
                icon: `<svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`,
                border: 'border-amber-500/30',
                bg: 'bg-[#18182e]/95',
                badgeBg: 'bg-amber-500/10',
                title: 'Attention'
            },
            info: {
                icon: `<svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
                border: 'border-indigo-500/30',
                bg: 'bg-[#18182e]/95',
                badgeBg: 'bg-indigo-500/10',
                title: 'Information'
            }
        };

        const currentConfig = config[normalizedType] || config.info;
        const container = getToastContainer();

        const toast = document.createElement('div');
        toast.className = `pointer-events-auto flex items-start gap-3 p-4 rounded-2xl ${currentConfig.bg} backdrop-blur-md border ${currentConfig.border} shadow-2xl shadow-black/50 transform transition-all duration-300 ease-out translate-x-12 opacity-0 select-none`;

        toast.innerHTML = `
            <div class="p-1 rounded-xl ${currentConfig.badgeBg} flex items-center justify-center">
                ${currentConfig.icon}
            </div>
            <div class="flex-1 min-w-0 pt-0.5">
                <div class="text-xs font-semibold text-slate-300 uppercase tracking-wider mb-0.5">${currentConfig.title}</div>
                <div class="text-sm font-medium text-white break-words leading-relaxed">${escapeHtml(message)}</div>
            </div>
            <button type="button" class="toast-close text-slate-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-white/5" aria-label="Fermer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        `;

        container.appendChild(toast);

        // Animation d'entrée
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-12', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        });

        // Fonction de fermeture
        let timeoutId;
        const closeToast = () => {
            clearTimeout(timeoutId);
            toast.classList.remove('translate-x-0', 'opacity-100');
            toast.classList.add('translate-x-12', 'opacity-0');
            setTimeout(() => {
                if (toast.parentElement) toast.remove();
            }, 300);
        };

        // Bouton de fermeture manuelle
        const closeBtn = toast.querySelector('.toast-close');
        if (closeBtn) closeBtn.addEventListener('click', closeToast);

        // Auto-fermeture
        timeoutId = setTimeout(closeToast, duration);

        return toast;
    }

    // Expose globalement
    window.showToast = showToast;

    // =========================================================
    // 2. LOADING SPINNERS & GESTION DES BOUTONS (Req A)
    // =========================================================

    const SPINNER_SVG = `
        <svg class="animate-spin -ml-1 mr-2.5 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    `;

    /**
     * Active ou désactive l'état de chargement sur un bouton
     * @param {HTMLButtonElement|HTMLAnchorElement} btn
     * @param {boolean} isLoading
     * @param {string} [loadingText='Enregistrement...']
     */
    function setButtonLoading(btn, isLoading, loadingText = 'Enregistrement...') {
        if (!btn) return;

        if (isLoading) {
            btn.dataset.originalHtml = btn.innerHTML;
            btn.dataset.originalPointerEvents = btn.style.pointerEvents;
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            btn.style.pointerEvents = 'none';
            btn.innerHTML = `${SPINNER_SVG}<span>${escapeHtml(loadingText)}</span>`;
        } else {
            if (btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
                delete btn.dataset.originalHtml;
            }
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
            btn.style.pointerEvents = btn.dataset.originalPointerEvents || '';
            delete btn.dataset.originalPointerEvents;
        }
    }

    window.setButtonLoading = setButtonLoading;

    /**
     * Affiche un spinner plein écran pour les chargements globaux
     * @param {string} [text='Chargement...']
     */
    function showLoading(text = 'Chargement...') {
        let overlay = document.getElementById('global-loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'global-loading-overlay';
            overlay.className = 'fixed inset-0 z-[9998] flex items-center justify-center bg-black/75 backdrop-blur-sm transition-opacity duration-200 opacity-0';
            overlay.innerHTML = `
                <div class="bg-[#18182e] border border-white/10 rounded-2xl p-6 shadow-2xl flex items-center gap-4 text-white">
                    <svg class="animate-spin h-7 w-7 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="global-loading-text" class="text-sm font-semibold tracking-wide">${escapeHtml(text)}</span>
                </div>
            `;
            document.body.appendChild(overlay);
        } else {
            const textEl = document.getElementById('global-loading-text');
            if (textEl) textEl.textContent = text;
        }

        requestAnimationFrame(() => {
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
        });
    }

    function hideLoading() {
        const overlay = document.getElementById('global-loading-overlay');
        if (overlay) {
            overlay.classList.remove('opacity-100');
            overlay.classList.add('opacity-0');
            setTimeout(() => overlay.remove(), 200);
        }
    }

    window.showLoading = showLoading;
    window.hideLoading = hideLoading;

    // =========================================================
    // 3. VALIDATION CÔTÉ CLIENT DES FORMULAIRES (Req C)
    // =========================================================

    /**
     * Valide un champ individuel
     * @param {HTMLElement} field
     * @returns {string|null} Message d'erreur ou null si valide
     */
    function validateField(field) {
        if (!field || field.disabled || field.type === 'hidden' || field.type === 'submit') {
            return null;
        }

        const value = (field.value || '').trim();
        const isRequired = field.hasAttribute('required') || field.required;

        // 1. Champ obligatoire
        if (isRequired && value === '') {
            const label = getFieldLabel(field);
            return `Le champ ${label} est obligatoire.`;
        }

        // Si le champ est vide et non obligatoire, pas d'autre validation
        if (value === '') {
            return null;
        }

        // 2. Format Email
        if (field.type === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                return "Format d'adresse email invalide.";
            }
        }

        // 3. Format URL
        if (field.type === 'url') {
            try {
                const url = new URL(value);
                if (url.protocol !== 'http:' && url.protocol !== 'https:') {
                    return "L'URL doit commencer par http:// ou https://";
                }
            } catch (_) {
                return "Format d'URL invalide (ex: https://example.com).";
            }
        }

        // 4. Nombre & Min / Max
        if (field.type === 'number') {
            const num = parseFloat(value);
            if (isNaN(num)) {
                return "Veuillez saisir un nombre valide.";
            }
            if (field.hasAttribute('min')) {
                const min = parseFloat(field.getAttribute('min'));
                if (num < min) {
                    return `La valeur doit être supérieure ou égale à ${min}.`;
                }
            }
            if (field.hasAttribute('max')) {
                const max = parseFloat(field.getAttribute('max'));
                if (num > max) {
                    return `La valeur doit être inférieure ou égale à ${max}.`;
                }
            }
        }

        // 5. Longueur min/max
        if (field.hasAttribute('minlength')) {
            const minLen = parseInt(field.getAttribute('minlength'), 10);
            if (value.length < minLen) {
                return `Ce champ doit contenir au moins ${minLen} caractères.`;
            }
        }
        if (field.hasAttribute('maxlength')) {
            const maxLen = parseInt(field.getAttribute('maxlength'), 10);
            if (value.length > maxLen) {
                return `Ce champ ne doit pas dépasser ${maxLen} caractères.`;
            }
        }

        return null;
    }

    function getFieldLabel(field) {
        const id = field.id;
        if (id) {
            const labelEl = document.querySelector(`label[for="${id}"]`);
            if (labelEl) {
                return `« ${labelEl.textContent.replace('*', '').trim()} »`;
            }
        }
        return `« ${field.name || 'Ce champ'} »`;
    }

    /**
     * Met à jour l'affichage d'erreur ou de succès sur un champ
     */
    function updateFieldValidationUI(field, errorMsg) {
        // Supprimer message d'erreur existant
        const existingError = field.parentElement.querySelector('.field-validation-error');
        if (existingError) existingError.remove();

        const defaultBorder = 'border-white/10';
        const errorClasses = ['border-red-500', 'focus:border-red-500', 'ring-1', 'ring-red-500/30'];
        const successClasses = ['border-emerald-500/50', 'focus:border-emerald-500'];

        if (errorMsg) {
            // État invalide
            field.classList.remove(...successClasses);
            field.classList.add(...errorClasses);

            const errorEl = document.createElement('p');
            errorEl.className = 'field-validation-error text-xs text-red-400 mt-1.5 flex items-center gap-1 font-medium animate-[fadeIn_0.15s_ease-out]';
            errorEl.innerHTML = `<span class="shrink-0">⚠</span><span>${escapeHtml(errorMsg)}</span>`;
            field.parentElement.appendChild(errorEl);
        } else {
            // État valide
            field.classList.remove(...errorClasses);
            if (field.value.trim() !== '') {
                field.classList.add(...successClasses);
            } else {
                field.classList.remove(...successClasses);
                field.classList.add(defaultBorder);
            }
        }
    }

    /**
     * Initialise la validation automatique sur tous les formulaires
     */
    function initFormValidation() {
        const forms = document.querySelectorAll('form:not([data-no-validate])');

        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, select, textarea');

            // Écoute des événements en direct sur chaque champ
            inputs.forEach(input => {
                const handler = () => {
                    const error = validateField(input);
                    updateFieldValidationUI(input, error);
                };

                input.addEventListener('input', handler);
                input.addEventListener('change', handler);
                input.addEventListener('blur', handler);
            });

            // Interception de la soumission du formulaire
            form.addEventListener('submit', function(e) {
                // Empêcher les doubles soumissions consécutives
                if (form.dataset.submitting === 'true') {
                    e.preventDefault();
                    return false;
                }

                let formHasErrors = false;
                let firstInvalidField = null;

                inputs.forEach(input => {
                    const error = validateField(input);
                    if (error) {
                        formHasErrors = true;
                        updateFieldValidationUI(input, error);
                        if (!firstInvalidField) firstInvalidField = input;
                    }
                });

                if (formHasErrors) {
                    e.preventDefault();
                    showToast('Veuillez corriger les champs obligatoires avant de soumettre.', 'warning');
                    if (firstInvalidField) {
                        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstInvalidField.focus();
                    }
                    return false;
                }

                // Si le formulaire est valide : passage en état de chargement et verrouillage anti-double envoi
                form.dataset.submitting = 'true';
                const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                if (submitBtn) {
                    const actionText = submitBtn.dataset.loadingText || (form.id.includes('modifier') ? 'Mise à jour...' : 'Enregistrement...');
                    setButtonLoading(submitBtn, true, actionText);
                }
            });
        });
    }

    // =========================================================
    // 4. API ERROR HANDLING & FETCH HELPER (Req D)
    // =========================================================

    /**
     * Client REST API standardisé avec gestion globale du feedback UI
     * @param {string} url - URL de la requête
     * @param {RequestInit} [options={}] - Options fetch
     * @returns {Promise<any>}
     */
    async function apiFetch(url, options = {}) {
        const method = (options.method || 'GET').toUpperCase();
        const isMutation = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method);

        // Afficher l'indicateur de chargement si demandé ou par défaut sur mutations
        if (options.showLoading !== false && isMutation) {
            showLoading(options.loadingText || 'Traitement en cours...');
        }

        try {
            const headers = options.headers || {};
            if (!(options.body instanceof FormData) && !headers['Content-Type']) {
                headers['Content-Type'] = 'application/json';
            }

            const response = await fetch(url, {
                ...options,
                headers
            });

            const data = await response.json().catch(() => null);

            // Gestion par code HTTP
            switch (response.status) {
                case 200:
                case 201:
                    if (isMutation && options.silentSuccess !== true) {
                        const successMsg = data?.message || (response.status === 201 ? 'Création effectuée avec succès.' : 'Opération réussie.');
                        showToast(successMsg, 'success');
                    }
                    break;

                case 400:
                    const badReqMsg = data?.message || 'Données fournies invalides. Veuillez vérifier votre saisie.';
                    showToast(badReqMsg, 'warning');
                    throw new Error(badReqMsg);

                case 404:
                    const notFoundMsg = data?.message || 'La ressource demandée est introuvable.';
                    showToast(notFoundMsg, 'error');
                    throw new Error(notFoundMsg);

                case 405:
                    const notAllowedMsg = data?.message || 'Méthode HTTP non autorisée pour cette ressource.';
                    showToast(notAllowedMsg, 'error');
                    throw new Error(notAllowedMsg);

                case 500:
                default:
                    if (!response.ok) {
                        const serverErrMsg = data?.message || `Erreur serveur inattendue (Code HTTP ${response.status}).`;
                        showToast(serverErrMsg, 'error');
                        throw new Error(serverErrMsg);
                    }
                    break;
            }

            return data;
        } catch (err) {
            // Erreur réseau ou interruption
            if (err.name === 'TypeError' || err.message.includes('Failed to fetch')) {
                showToast('Problème de connexion avec le serveur. Vérifiez votre réseau.', 'error');
            }
            throw err;
        } finally {
            hideLoading();
        }
    }

    window.apiFetch = apiFetch;

    // =========================================================
    // 5. MODALE DE SUPPRESSION (Req E - Delete Consistency)
    // =========================================================

    function confirmerSuppression(url, nom, type = 'cet élément') {
        fermerModal();

        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm transition-opacity duration-200';
        overlay.id = 'confirm-modal';

        overlay.innerHTML = `
            <div class="bg-[#18182e] border border-white/10 rounded-2xl p-6 max-w-md w-full shadow-2xl text-center transform transition-transform duration-200 scale-100">
                <div class="w-14 h-14 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-400 text-2xl flex items-center justify-center mx-auto mb-4">
                    🗑️
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Confirmer la suppression</h3>
                <p class="text-sm text-slate-300 mb-2">
                    Voulez-vous vraiment supprimer <span class="font-semibold text-slate-100">${escapeHtml(type)}</span> :
                </p>
                <div class="p-3 bg-[#222240] rounded-xl text-indigo-400 font-semibold text-sm mb-3 break-words border border-white/5">
                    ${escapeHtml(nom)}
                </div>
                <p class="text-xs text-red-400 font-medium mb-6">⚠️ Cette action est irréversible.</p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" class="px-5 py-2.5 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-sm font-medium transition-all" onclick="fermerModal()">
                        ✕ Annuler
                    </button>
                    <a href="${url}" id="btn-confirm-delete" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white text-sm font-semibold shadow-lg shadow-red-600/30 transition-all flex items-center gap-2">
                        <span>🗑️</span> Supprimer
                    </a>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        const deleteBtn = document.getElementById('btn-confirm-delete');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                setButtonLoading(deleteBtn, true, 'Suppression...');
            });
        }

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) fermerModal();
        });

        document.addEventListener('keydown', function handleEsc(e) {
            if (e.key === 'Escape') {
                fermerModal();
                document.removeEventListener('keydown', handleEsc);
            }
        });
    }

    function fermerModal() {
        const modal = document.getElementById('confirm-modal');
        if (modal) {
            modal.classList.add('opacity-0');
            setTimeout(() => modal.remove(), 150);
        }
    }

    window.confirmerSuppression = confirmerSuppression;
    window.fermerModal = fermerModal;

    // =========================================================
    // 6. INITIALISATION SIDEBAR & PREVIEWS D'IMAGE
    // =========================================================

    function initSidebar() {
        const sidebar   = document.getElementById('sidebar');
        const overlay   = document.getElementById('sidebarOverlay');
        const hamburger = document.getElementById('hamburgerBtn');

        if (!sidebar || !overlay || !hamburger) return;

        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    }

    function initImagePreviews() {
        const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');

        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                const previewId = this.getAttribute('data-preview');
                const previewEl = document.getElementById(previewId);

                if (!previewEl || !this.files || !this.files[0]) return;

                const file = this.files[0];

                if (!file.type.startsWith('image/')) {
                    showToast('Le fichier sélectionné n\'est pas une image.', 'warning');
                    this.value = '';
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    showToast('L\'image ne doit pas dépasser 5 Mo.', 'warning');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewEl.innerHTML = `<img src="${e.target.result}" alt="Aperçu" class="w-full h-full object-cover rounded-xl animate-[fadeIn_0.2s_ease-out]">`;
                };
                reader.readAsDataURL(file);
            });
        });
    }

    function initAlertBanners() {
        const alerts = document.querySelectorAll('[data-alert]');
        alerts.forEach(alert => {
            const closeBtn = alert.querySelector('[data-alert-close]');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => dismissAlert(alert));
            }

            // Déclenche également une notification Toast synchronisée
            const text = alert.textContent.replace(/✕|✅|❌/g, '').trim();
            const isSuccess = alert.classList.contains('border-emerald-500/30') || alert.textContent.includes('✅');
            const isError = alert.classList.contains('border-red-500/30') || alert.textContent.includes('❌');
            const toastType = isSuccess ? 'success' : (isError ? 'error' : 'info');

            if (text && !alert.dataset.toastShown) {
                alert.dataset.toastShown = 'true';
                showToast(text, toastType);
            }

            setTimeout(() => dismissAlert(alert), 5000);
        });
    }

    function dismissAlert(alert) {
        if (!alert) return;
        alert.style.transition = 'opacity 0.3s ease, transform 0.3s ease, max-height 0.3s ease, margin 0.3s ease, padding 0.3s ease';
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-6px)';
        alert.style.maxHeight = '0';
        alert.style.margin = '0';
        alert.style.padding = '0';
        alert.style.overflow = 'hidden';
        setTimeout(() => alert.remove(), 300);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(String(str)));
        return div.innerHTML;
    }

    // =========================================================
    // 7. INITIALISATION GLOBALE AU CHARGEMENT DU DOM
    // =========================================================

    document.addEventListener('DOMContentLoaded', function() {
        initSidebar();
        initAlertBanners();
        initImagePreviews();
        initFormValidation();
    });

})();
