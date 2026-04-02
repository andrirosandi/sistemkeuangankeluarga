/**
 * Smart Table Handler for Persistent Selection
 */
class SmartTableSelection {
    constructor(options) {
        this.selectedIds = new Set();
        this.config = {
            selectAllId: 'select-all',
            checkItemClass: 'check-item',
            batchBarId: 'batch-action-bar',
            countId: 'selected-count',
            cancelBtnId: 'btn-cancel-all',
            tableBodyClass: '.list',
            ...options
        };

        this.init();
    }

    init() {
        this.selectAllHeader = document.getElementById(this.config.selectAllId);
        this.tableBody = document.querySelector(this.config.tableBodyClass);
        this.batchBar = document.getElementById(this.config.batchBarId);
        this.selectedCountSpan = document.getElementById(this.config.countId);
        this.cancelBtn = document.getElementById(this.config.cancelBtnId);

        this.registerEvents();
    }

    registerEvents() {
        // Select All (Header)
        this.selectAllHeader?.addEventListener('click', (e) => {
            const isChecked = e.target.checked;
            const checkboxes = document.querySelectorAll(`.${this.config.checkItemClass}`);
            checkboxes.forEach(cb => {
                cb.checked = isChecked;
                if (isChecked) this.selectedIds.add(cb.value);
                else this.selectedIds.delete(cb.value);
            });
            this.updateUI();
        });

        // Item Change (Delegation)
        this.tableBody?.addEventListener('change', (e) => {
            if (e.target.classList.contains(this.config.checkItemClass)) {
                if (e.target.checked) this.selectedIds.add(e.target.value);
                else this.selectedIds.delete(e.target.value);
                
                this.syncCheckboxes();
                this.updateUI();
            }
        });

        // Global Cancel
        this.cancelBtn?.addEventListener('click', () => {
            this.clear();
        });
    }

    syncCheckboxes() {
        const checkboxes = document.querySelectorAll(`.${this.config.checkItemClass}`);
        checkboxes.forEach(cb => {
            cb.checked = this.selectedIds.has(cb.value);
        });
        
        if (checkboxes.length > 0) {
            const visibleChecked = document.querySelectorAll(`.${this.config.checkItemClass}:checked`).length;
            if (this.selectAllHeader) {
                this.selectAllHeader.checked = (visibleChecked === checkboxes.length);
                this.selectAllHeader.indeterminate = (visibleChecked > 0 && visibleChecked < checkboxes.length);
            }
        }
    }

    updateUI() {
        const count = this.selectedIds.size;
        if (this.batchBar) {
            if (count > 0) {
                this.batchBar.classList.remove('d-none');
                if (this.selectedCountSpan) this.selectedCountSpan.innerText = count;
            } else {
                this.batchBar.classList.add('d-none');
                if (this.selectAllHeader) {
                    this.selectAllHeader.checked = false;
                    this.selectAllHeader.indeterminate = false;
                }
            }
        }
    }

    clear() {
        this.selectedIds.clear();
        this.syncCheckboxes();
        this.updateUI();
    }

    getSelectedIds() {
        return Array.from(this.selectedIds);
    }
}

// Global helper to init a table
window.initSmartTableSelection = function(options) {
    return new SmartTableSelection(options);
};
