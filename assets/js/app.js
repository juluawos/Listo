/**
 * Listo - JavaScript Interativo
 * Sistema de Toasts, Modais Nativos Customizados e AJAX para Listas
 */

// ========================================================
// SISTEMA DE TOASTS (Notificações Flutuantes)
// ========================================================
const ListoToast = {
    container: null,

    init() {
        if (!this.container) {
            let el = document.getElementById('toast-container');
            if (!el) {
                el = document.createElement('div');
                el.id = 'toast-container';
                el.className = 'fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full px-4 sm:px-0 pointer-events-none';
                document.body.appendChild(el);
            }
            this.container = el;
        }
    },

    show(message, type = 'info', duration = 4000) {
        this.init();

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto flex items-start gap-3 p-4 rounded-2xl shadow-xl border animate-toast transition-all backdrop-blur-md bg-white/95';

        // Estilos por tipo
        let iconSvg = '';
        let borderColor = 'border-slate-200';
        let textColor = 'text-slate-800';

        switch (type) {
            case 'success':
                borderColor = 'border-emerald-200 bg-emerald-50/95';
                textColor = 'text-emerald-900';
                iconSvg = `
                    <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>`;
                break;
            case 'error':
                borderColor = 'border-rose-200 bg-rose-50/95';
                textColor = 'text-rose-900';
                iconSvg = `
                    <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>`;
                break;
            case 'warning':
                borderColor = 'border-amber-200 bg-amber-50/95';
                textColor = 'text-amber-900';
                iconSvg = `
                    <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>`;
                break;
            default: // info
                borderColor = 'border-indigo-200 bg-indigo-50/95';
                textColor = 'text-indigo-900';
                iconSvg = `
                    <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>`;
                break;
        }

        toast.classList.add(...borderColor.split(' '));

        toast.innerHTML = `
            ${iconSvg}
            <div class="flex-1 text-sm font-medium ${textColor} pt-1 leading-snug">
                ${message}
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition" onclick="this.closest('.animate-toast').remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        `;

        this.container.appendChild(toast);

        // Auto dismiss
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                setTimeout(() => toast.remove(), 250);
            }
        }, duration);
    }
};

// ========================================================
// SISTEMA DE MODAIS CUSTOMIZADOS (Substitui confirm/alert)
// ========================================================
const ListoModal = {
    overlay: null,

    init() {
        if (!this.overlay) {
            let el = document.getElementById('listo-modal-overlay');
            if (!el) {
                el = document.createElement('div');
                el.id = 'listo-modal-overlay';
                el.className = 'fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 transition-opacity';
                document.body.appendChild(el);
            }
            this.overlay = el;
        }
    },

    confirm({ title = 'Confirmação', message = 'Deseja realmente continuar?', confirmText = 'Confirmar', cancelText = 'Cancelar', confirmColor = 'rose', onConfirm = null }) {
        this.init();

        const colorClasses = {
            rose: 'bg-rose-600 hover:bg-rose-700 focus:ring-rose-500',
            indigo: 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
            emerald: 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500',
            amber: 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500'
        }[confirmColor] || 'bg-rose-600 hover:bg-rose-700 focus:ring-rose-500';

        this.overlay.innerHTML = `
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-md w-full p-6 animate-modal">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">${title}</h3>
                </div>
                <p class="text-slate-600 text-sm mb-6 leading-relaxed">${message}</p>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" id="listo-modal-cancel" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-medium text-sm transition">
                        ${cancelText}
                    </button>
                    <button type="button" id="listo-modal-confirm" class="px-5 py-2.5 rounded-xl text-white font-semibold text-sm shadow-md transition ${colorClasses}">
                        ${confirmText}
                    </button>
                </div>
            </div>
        `;

        this.overlay.classList.remove('hidden');
        this.overlay.classList.add('flex');

        const close = () => {
            this.overlay.classList.add('hidden');
            this.overlay.classList.remove('flex');
            this.overlay.innerHTML = '';
        };

        document.getElementById('listo-modal-cancel').onclick = close;
        document.getElementById('listo-modal-confirm').onclick = () => {
            close();
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        };

        // Fechar ao clicar fora
        this.overlay.onclick = (e) => {
            if (e.target === this.overlay) close();
        };
    },

    alert({ title = 'Aviso', message = '', buttonText = 'Entendido', onConfirm = null }) {
        this.init();

        this.overlay.innerHTML = `
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-md w-full p-6 animate-modal">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">${title}</h3>
                </div>
                <p class="text-slate-600 text-sm mb-6 leading-relaxed">${message}</p>
                <div class="flex items-center justify-end">
                    <button type="button" id="listo-modal-alert-btn" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-md transition">
                        ${buttonText}
                    </button>
                </div>
            </div>
        `;

        this.overlay.classList.remove('hidden');
        this.overlay.classList.add('flex');

        const close = () => {
            this.overlay.classList.add('hidden');
            this.overlay.classList.remove('flex');
            this.overlay.innerHTML = '';
            if (typeof onConfirm === 'function') onConfirm();
        };

        document.getElementById('listo-modal-alert-btn').onclick = close;
        this.overlay.onclick = (e) => {
            if (e.target === this.overlay) close();
        };
    }
};

// ========================================================
// CONTROLE DE MODAIS ESTÁTICOS NA PÁGINA (ex: Criar Lista)
// ========================================================
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }
}

// ========================================================
// TOGGLE DE ITEM VIA AJAX COM ATUALIZAÇÃO IMEDIATA
// ========================================================
async function toggleItemStatus(checkbox, itemId, actionUrl) {
    const isChecked = checkbox.checked ? 1 : 0;
    const itemCard = checkbox.closest('[data-item-card]');
    const titleEl = itemCard ? itemCard.querySelector('.item-title') : null;

    // Atualização visual otimista
    if (titleEl) {
        if (isChecked) {
            titleEl.classList.add('line-through', 'text-slate-400');
            titleEl.classList.remove('text-slate-800');
        } else {
            titleEl.classList.remove('line-through', 'text-slate-400');
            titleEl.classList.add('text-slate-800');
        }
    }

    try {
        const formData = new FormData();
        formData.append('item_id', itemId);
        formData.append('concluido', isChecked);

        const response = await fetch(actionUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (data.success) {
            ListoToast.show(data.message || (isChecked ? 'Item concluído!' : 'Item reaberto.'), 'success', 2000);
            
            // Atualizar contadores na tela se existirem
            updatePageCounters();
        } else {
            // Reverter em caso de falha
            checkbox.checked = !isChecked;
            if (titleEl) {
                titleEl.classList.toggle('line-through', !isChecked);
                titleEl.classList.toggle('text-slate-400', !isChecked);
            }
            ListoToast.show(data.message || 'Erro ao atualizar item.', 'error');
        }
    } catch (err) {
        checkbox.checked = !isChecked;
        if (titleEl) {
            titleEl.classList.toggle('line-through', !isChecked);
            titleEl.classList.toggle('text-slate-400', !isChecked);
        }
        ListoToast.show('Falha na comunicação com o servidor.', 'error');
    }
}

// Atualiza contadores locais de itens concluídos
function updatePageCounters() {
    const allCheckboxes = document.querySelectorAll('.item-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
    
    const countTotal = allCheckboxes.length;
    const countDone = checkedCheckboxes.length;

    const elTotal = document.getElementById('counter-total');
    const elDone = document.getElementById('counter-done');
    const elProgress = document.getElementById('progress-bar');

    if (elTotal) elTotal.innerText = countTotal;
    if (elDone) elDone.innerText = countDone;

    if (elProgress && countTotal > 0) {
        const pct = Math.round((countDone / countTotal) * 100);
        elProgress.style.width = pct + '%';
        const elPct = document.getElementById('counter-percentage');
        if (elPct) elPct.innerText = pct + '%';
    }
}

// ========================================================
// INICIALIZAÇÃO DE LISTENERS GLOBAIS
// ========================================================
document.addEventListener('DOMContentLoaded', () => {
    // 1. Interceptar cliques com data-confirm (Substitui confirm() nativo)
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-confirm]');
        if (!trigger) return;

        // Se for um link ou botão dentro de form
        e.preventDefault();
        const message = trigger.getAttribute('data-confirm') || 'Tem certeza que deseja continuar?';
        const title = trigger.getAttribute('data-confirm-title') || 'Confirmar ação';
        const color = trigger.getAttribute('data-confirm-color') || 'rose';

        ListoModal.confirm({
            title: title,
            message: message,
            confirmColor: color,
            onConfirm: () => {
                if (trigger.tagName === 'A') {
                    window.location.href = trigger.href;
                } else if (trigger.type === 'submit' && trigger.form) {
                    trigger.form.submit();
                } else if (trigger.tagName === 'BUTTON' && trigger.form) {
                    trigger.form.submit();
                }
            }
        });
    });

    // 2. Botões para abrir/fechar modais via data attributes
    document.addEventListener('click', (e) => {
        const openBtn = e.target.closest('[data-modal-open]');
        if (openBtn) {
            const modalId = openBtn.getAttribute('data-modal-open');
            openModal(modalId);
        }

        const closeBtn = e.target.closest('[data-modal-close]');
        if (closeBtn) {
            const modalId = closeBtn.getAttribute('data-modal-close');
            closeModal(modalId);
        }
    });

    // 3. Botão de copiar texto (código de convite)
    document.addEventListener('click', (e) => {
        const copyBtn = e.target.closest('[data-copy-text]');
        if (!copyBtn) return;

        const textToCopy = copyBtn.getAttribute('data-copy-text');
        navigator.clipboard.writeText(textToCopy).then(() => {
            ListoToast.show('Copiado para a área de transferência!', 'success');
        }).catch(() => {
            ListoToast.show('Não foi possível copiar.', 'error');
        });
    });
});
