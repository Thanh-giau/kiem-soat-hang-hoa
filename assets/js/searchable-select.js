/**
 * Searchable Select Component
 * High-performance, accent-insensitive Vietnamese search combobox for products
 */

(function(window) {
    'use strict';

    // Helper: Remove Vietnamese tones / diacritics for flexible fuzzy searching
    function removeVietnameseTones(str) {
        if (!str) return '';
        str = String(str).toLowerCase();
        str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
        str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
        str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
        str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
        str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
        str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
        str = str.replace(/đ/g, "d");
        str = str.replace(/\u0300|\u0301|\u0303|\u0309|\u0323/g, ""); // Marks
        str = str.replace(/\u02C6|\u0306|\u031B/g, ""); // Marks
        return str.trim();
    }

    // Helper: Escape HTML special chars
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Helper: Highlight matching substring in text
    function highlightMatch(text, query) {
        if (!query || !text) return escapeHtml(text);
        const normText = removeVietnameseTones(text);
        const normQuery = removeVietnameseTones(query);
        const idx = normText.indexOf(normQuery);
        if (idx === -1) return escapeHtml(text);

        const before = text.substring(0, idx);
        const match = text.substring(idx, idx + query.length);
        const after = text.substring(idx + query.length);
        return `${escapeHtml(before)}<span class="searchable-select-match">${escapeHtml(match)}</span>${escapeHtml(after)}`;
    }

    class SearchableSelect {
        constructor(selectElement, options = {}) {
            if (!selectElement || selectElement.searchableInstance) return;
            this.select = selectElement;
            this.options = Object.assign({
                placeholder: '🔍 Gõ tên hoặc mã SP để tìm...',
                noResultsText: 'Không tìm thấy sản phẩm phù hợp',
                autoFocusNext: true
            }, options);

            this.items = [];
            this.filteredItems = [];
            this.highlightedIndex = -1;
            this.isOpen = false;

            this.init();
        }

        init() {
            this.parseOptions();
            this.buildDOM();
            this.bindEvents();
            this.syncFromSelect();
            this.select.searchableInstance = this;
            this.select.classList.add('has-searchable-select');
        }

        parseOptions() {
            this.items = [];
            Array.from(this.select.options).forEach(opt => {
                if (!opt.value) return; // Skip placeholder
                const text = opt.textContent.trim();
                const dvt = opt.dataset.dvt || '';

                // Extract product info: [ma_sp] ten_sp (dvt - nhom_sp) ★
                let maSp = '';
                let tenSp = text;
                let nhomSp = '';
                let isKiemKe = text.includes('★');

                const maMatch = text.match(/\[(.*?)\]/);
                if (maMatch) {
                    maSp = maMatch[1];
                }

                // Remove [ma_sp] from name
                let cleanName = text.replace(/\[.*?\]\s*/, '');
                // Extract (dvt - nhom)
                const parenMatch = cleanName.match(/\((.*?)\)/);
                if (parenMatch) {
                    const inside = parenMatch[1];
                    const parts = inside.split('-').map(s => s.trim());
                    if (parts.length > 1) {
                        nhomSp = parts[1];
                    }
                    cleanName = cleanName.replace(/\(.*?\)/, '');
                }
                cleanName = cleanName.replace('★', '').trim();

                this.items.push({
                    value: opt.value,
                    rawText: text,
                    maSp: maSp,
                    tenSp: cleanName || text,
                    dvt: dvt,
                    nhomSp: nhomSp,
                    isKiemKe: isKiemKe,
                    normSearch: removeVietnameseTones(`${maSp} ${cleanName} ${nhomSp} ${dvt}`)
                });
            });
            this.filteredItems = [...this.items];
        }

        buildDOM() {
            // Container wrapper
            this.wrapper = document.createElement('div');
            this.wrapper.className = 'searchable-select-wrapper';

            // Input box
            this.inputBox = document.createElement('div');
            this.inputBox.className = 'searchable-select-input-box';

            this.searchIcon = document.createElement('i');
            this.searchIcon.className = 'fa-solid fa-magnifying-glass searchable-select-icon-search';

            this.input = document.createElement('input');
            this.input.type = 'text';
            this.input.className = 'searchable-select-input';
            this.input.placeholder = this.options.placeholder;
            this.input.autocomplete = 'off';
            this.input.spellcheck = false;

            this.actions = document.createElement('div');
            this.actions.className = 'searchable-select-actions';

            this.clearBtn = document.createElement('button');
            this.clearBtn.type = 'button';
            this.clearBtn.className = 'searchable-select-clear';
            this.clearBtn.innerHTML = '&times;';
            this.clearBtn.title = 'Xóa lựa chọn';

            this.arrow = document.createElement('span');
            this.arrow.className = 'searchable-select-arrow';
            this.arrow.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';

            this.actions.appendChild(this.clearBtn);
            this.actions.appendChild(this.arrow);

            this.inputBox.appendChild(this.searchIcon);
            this.inputBox.appendChild(this.input);
            this.inputBox.appendChild(this.actions);
            this.wrapper.appendChild(this.inputBox);

            // Floating Dropdown menu (appended to document.body to prevent clipping by overflow tables)
            this.dropdown = document.createElement('div');
            this.dropdown.className = 'searchable-select-dropdown';

            this.listEl = document.createElement('div');
            this.listEl.className = 'searchable-select-list';

            this.emptyEl = document.createElement('div');
            this.emptyEl.className = 'searchable-select-empty';
            this.emptyEl.innerHTML = `
                <i class="fa-solid fa-box-open"></i>
                <span>${escapeHtml(this.options.noResultsText)}</span>
            `;

            this.hintEl = document.createElement('div');
            this.hintEl.className = 'searchable-select-hint';
            this.hintEl.innerHTML = `
                <span><i class="fa-solid fa-arrow-up"></i> <i class="fa-solid fa-arrow-down"></i> Điều hướng</span>
                <span>[Enter] Chọn</span>
            `;

            this.dropdown.appendChild(this.listEl);
            this.dropdown.appendChild(this.emptyEl);
            this.dropdown.appendChild(this.hintEl);
            document.body.appendChild(this.dropdown);

            // Insert wrapper right next to original select
            this.select.parentNode.insertBefore(this.wrapper, this.select);
        }

        bindEvents() {
            // Focus & click on input
            this.input.addEventListener('focus', () => {
                this.input.select();
                this.open();
            });

            this.input.addEventListener('click', () => {
                if (!this.isOpen) {
                    this.open();
                }
            });

            // Typing in search input
            this.input.addEventListener('input', () => {
                const query = this.input.value.trim();
                this.filter(query);
                if (!this.isOpen) this.open();
            });

            // Keyboard navigation
            this.input.addEventListener('keydown', (e) => {
                if (!this.isOpen && (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter')) {
                    this.open();
                    e.preventDefault();
                    return;
                }

                if (!this.isOpen) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.moveHighlight(1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    this.moveHighlight(-1);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredItems.length) {
                        this.selectItem(this.filteredItems[this.highlightedIndex]);
                    } else if (this.filteredItems.length > 0) {
                        this.selectItem(this.filteredItems[0]);
                    }
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    this.close();
                } else if (e.key === 'Tab') {
                    this.close();
                }
            });

            // Clear button
            this.clearBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                this.clearSelection();
                this.input.focus();
            });

            // Arrow toggle
            this.arrow.addEventListener('click', (e) => {
                e.stopPropagation();
                if (this.isOpen) {
                    this.close();
                } else {
                    this.input.focus();
                    this.open();
                }
            });

            // Close when clicking outside
            this.onDocClick = (e) => {
                if (!this.wrapper.contains(e.target) && !this.dropdown.contains(e.target)) {
                    this.close();
                }
            };
            document.addEventListener('mousedown', this.onDocClick);

            // Reposition dropdown on scroll/resize
            this.onReposition = () => {
                if (this.isOpen) this.positionDropdown();
            };
            window.addEventListener('scroll', this.onReposition, true);
            window.addEventListener('resize', this.onReposition);

            // Sync when original select changes programmatically
            this.select.addEventListener('change', () => {
                this.syncFromSelect();
            });
        }

        positionDropdown() {
            if (!this.inputBox) return;
            const rect = this.inputBox.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            const dropdownHeight = 300; // Expected max height

            this.dropdown.style.width = `${Math.max(rect.width, 320)}px`;
            this.dropdown.style.left = `${rect.left}px`;

            if (spaceBelow < dropdownHeight && spaceAbove > spaceBelow) {
                // Flip upward
                this.dropdown.style.bottom = `${window.innerHeight - rect.top + 4}px`;
                this.dropdown.style.top = 'auto';
            } else {
                // Regular downward
                this.dropdown.style.top = `${rect.bottom + 4}px`;
                this.dropdown.style.bottom = 'auto';
            }
        }

        open() {
            if (this.isOpen) return;
            this.isOpen = true;
            this.wrapper.classList.add('is-open');
            this.positionDropdown();
            this.dropdown.classList.add('is-open');
            this.renderList();
        }

        close() {
            if (!this.isOpen) return;
            this.isOpen = false;
            this.wrapper.classList.remove('is-open');
            this.dropdown.classList.remove('is-open');
            this.highlightedIndex = -1;

            // If input is empty or invalid, restore current selected item text
            this.syncFromSelect();
        }

        filter(query) {
            const normQuery = removeVietnameseTones(query);
            if (!normQuery) {
                this.filteredItems = [...this.items];
            } else {
                this.filteredItems = this.items.filter(item => {
                    return item.normSearch.includes(normQuery);
                });
            }
            this.highlightedIndex = this.filteredItems.length > 0 ? 0 : -1;
            this.renderList(query);
        }

        renderList(query = '') {
            this.listEl.innerHTML = '';

            if (this.filteredItems.length === 0) {
                this.listEl.style.display = 'none';
                this.emptyEl.style.display = 'flex';
                this.hintEl.style.display = 'none';
                return;
            }

            this.listEl.style.display = 'block';
            this.emptyEl.style.display = 'none';
            this.hintEl.style.display = 'flex';

            const curVal = this.select.value;

            this.filteredItems.forEach((item, index) => {
                const itemEl = document.createElement('div');
                itemEl.className = 'searchable-select-item';
                if (index === this.highlightedIndex) itemEl.classList.add('is-highlighted');
                if (item.value == curVal) itemEl.classList.add('is-selected');

                const titleHtml = highlightMatch(item.tenSp, query);
                const codeHtml = highlightMatch(item.maSp, query);

                itemEl.innerHTML = `
                    <div class="searchable-select-item-main">
                        <div class="searchable-select-item-title">${titleHtml}</div>
                        <div class="searchable-select-item-meta">
                            <span class="searchable-select-badge-code">${codeHtml}</span>
                            ${item.dvt ? `<span class="searchable-select-badge-dvt">${escapeHtml(item.dvt)}</span>` : ''}
                            ${item.nhomSp ? `<span class="searchable-select-badge-cat">${escapeHtml(item.nhomSp)}</span>` : ''}
                            ${item.isKiemKe ? `<span class="searchable-select-badge-star"><i class="fa-solid fa-star"></i> Kiểm kê</span>` : ''}
                        </div>
                    </div>
                `;

                itemEl.addEventListener('mouseenter', () => {
                    this.highlightedIndex = index;
                    this.updateHighlight();
                });

                itemEl.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    this.selectItem(item);
                });

                this.listEl.appendChild(itemEl);
            });

            this.scrollToHighlighted();
        }

        moveHighlight(step) {
            if (this.filteredItems.length === 0) return;
            this.highlightedIndex += step;
            if (this.highlightedIndex < 0) this.highlightedIndex = this.filteredItems.length - 1;
            if (this.highlightedIndex >= this.filteredItems.length) this.highlightedIndex = 0;
            this.updateHighlight();
            this.scrollToHighlighted();
        }

        updateHighlight() {
            const items = this.listEl.querySelectorAll('.searchable-select-item');
            items.forEach((el, idx) => {
                el.classList.toggle('is-highlighted', idx === this.highlightedIndex);
            });
        }

        scrollToHighlighted() {
            const items = this.listEl.querySelectorAll('.searchable-select-item');
            if (items[this.highlightedIndex]) {
                const el = items[this.highlightedIndex];
                const top = el.offsetTop;
                const bottom = top + el.offsetHeight;
                const listTop = this.listEl.scrollTop;
                const listBottom = listTop + this.listEl.offsetHeight;

                if (top < listTop) {
                    this.listEl.scrollTop = top;
                } else if (bottom > listBottom) {
                    this.listEl.scrollTop = bottom - this.listEl.offsetHeight;
                }
            }
        }

        selectItem(item) {
            if (!item) return;
            this.select.value = item.value;
            this.input.value = `[${item.maSp}] ${item.tenSp}`;
            this.inputBox.classList.add('is-selected');
            this.clearBtn.style.display = 'inline-flex';

            // Trigger native change event so all page listeners (recalculateTotals, handleProductChange) fire!
            this.select.dispatchEvent(new Event('change', { bubbles: true }));

            this.close();

            // Auto focus to quantity input in the same row
            if (this.options.autoFocusNext) {
                const row = this.wrapper.closest('tr, .item-row, .nvl-row');
                if (row) {
                    const nextInput = row.querySelector('.input-qty, input[type="number"]');
                    if (nextInput) {
                        nextInput.focus();
                        nextInput.select();
                    }
                }
            }
        }

        clearSelection() {
            this.select.value = '';
            this.input.value = '';
            this.inputBox.classList.remove('is-selected');
            this.clearBtn.style.display = 'none';
            this.filteredItems = [...this.items];
            this.select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        syncFromSelect() {
            const val = this.select.value;
            if (!val) {
                this.input.value = '';
                this.inputBox.classList.remove('is-selected');
                this.clearBtn.style.display = 'none';
                return;
            }

            const found = this.items.find(i => i.value == val);
            if (found) {
                this.input.value = `[${found.maSp}] ${found.tenSp}`;
                this.inputBox.classList.add('is-selected');
                this.clearBtn.style.display = 'inline-flex';
            } else {
                const opt = this.select.options[this.select.selectedIndex];
                if (opt && opt.value) {
                    this.input.value = opt.textContent.trim();
                    this.inputBox.classList.add('is-selected');
                    this.clearBtn.style.display = 'inline-flex';
                } else {
                    this.input.value = '';
                    this.inputBox.classList.remove('is-selected');
                    this.clearBtn.style.display = 'none';
                }
            }
        }

        setValue(val) {
            this.select.value = val;
            this.syncFromSelect();
            this.select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        destroy() {
            document.removeEventListener('mousedown', this.onDocClick);
            window.removeEventListener('scroll', this.onReposition, true);
            window.removeEventListener('resize', this.onReposition);
            if (this.dropdown && this.dropdown.parentNode) {
                this.dropdown.parentNode.removeChild(this.dropdown);
            }
            if (this.wrapper && this.wrapper.parentNode) {
                this.wrapper.parentNode.removeChild(this.wrapper);
            }
            this.select.classList.remove('has-searchable-select');
            delete this.select.searchableInstance;
        }
    }

    // Global helper function to initialize on any element or selector
    window.initSearchableSelect = function(elementOrSelector, options = {}) {
        if (typeof elementOrSelector === 'string') {
            const elements = document.querySelectorAll(elementOrSelector);
            const instances = [];
            elements.forEach(el => {
                instances.push(new SearchableSelect(el, options));
            });
            return instances;
        } else if (elementOrSelector instanceof HTMLElement) {
            return new SearchableSelect(elementOrSelector, options);
        }
    };

    window.SearchableSelect = SearchableSelect;

})(window);
