@php
    $component = $getComponent();
    $treeJson = \Illuminate\Support\Js::from($component->getTree());
    $labelKey = $component->getLabelKey();
    $valueKey = $component->getValueKey();
    $childrenKey = $component->getChildrenKey();
    $isSearchable = $component->isSearchable();
    $statePath = $getStatePath();
    $placeholder = $getLabel() ?? 'Seleziona...';
@endphp

<div wire:ignore x-data="{
    /* ── configurazione ── */
    labelKey: @js($labelKey),
    valueKey: @js($valueKey),
    childrenKey: @js($childrenKey),
    searchable: @js($isSearchable),

    /* ── stato ── */
    open: false,
    search: '',
    openGroups: {},

    /* stato Filament sincronizzato via Livewire */
    state: $wire.entangle('{{ $statePath }}').live,

    /* ── init ── */
    init() {
        if (!Array.isArray(this.state)) {
            this.state = this.state ? [this.state] : [];
        }
    },

    /* ── flatten dell'albero ────────────────────────────── */
    /*
     * Trasforma l'albero ricorsivo in una lista piatta,
     * conservando profondità e percorso degli antenati.
     * Il percorso è un array di label (es. ['Animali','Uccelli'])
     * per evitare collisioni tra nodi con prefisso comune.
     */
    flatten(nodes, depth, ancestorPath) {
        depth = depth ?? 0;
        ancestorPath = ancestorPath ?? [];
        const result = [];
        for (const node of nodes) {
            const label = node[this.labelKey];
            const hasKids = Array.isArray(node[this.childrenKey]) && node[this.childrenKey].length > 0;
            const path = [...ancestorPath, label];
            const pathKey = path.join('\x00'); /* separatore non-stampabile */

            result.push({
                label,
                value: hasKids ? null : node[this.valueKey],
                depth,
                isGroup: hasKids,
                path,
                pathKey,
                ancestorPathKey: ancestorPath.join('\x00'),
            });

            if (hasKids) {
                result.push(...this.flatten(node[this.childrenKey], depth + 1, path));
            }
        }
        return result;
    },

    get flatItems() {
        return this.flatten({{ $treeJson }});
    },

    /* ── visibilità ─────────────────────────────────────── */

    /*
     * Un item è discendente di un gruppo se il pathKey dell'item
     * inizia con il pathKey del gruppo + il separatore.
     */
    isDescendantOf(item, group) {
        return item.ancestorPathKey === group.pathKey ||
            item.ancestorPathKey.startsWith(group.pathKey + '\x00');
    },

    isVisible(item) {
        if (this.search.trim() !== '') {
            const q = this.search.trim().toLowerCase();
            if (item.isGroup) {
                /* mostra il gruppo solo se almeno un figlio foglia matcha */
                return this.flatItems.some(
                    i => !i.isGroup && this.isDescendantOf(i, item) && i.label.toLowerCase().includes(q)
                );
            }
            /* foglia: compare la label con la query */
            return item.label.toLowerCase().includes(q);
        }

        /* senza ricerca: rispetta lo stato aperto/chiuso dei gruppi antenati */
        if (item.depth === 0) return true;
        const parts = item.path.slice(0, -1); /* tutti gli antenati */
        for (let i = 1; i <= parts.length; i++) {
            const key = parts.slice(0, i).join('\x00');
            if (this.openGroups[key] === false) return false;
        }
        return true;
    },

    /* ── apri / chiudi gruppi ───────────────────────────── */

    toggleGroupOpen(pathKey) {
        this.openGroups[pathKey] = !(this.openGroups[pathKey] ?? true);
    },

    isGroupOpen(pathKey) {
        return this.openGroups[pathKey] ?? true;
    },

    /* ── selezione ──────────────────────────────────────── */

    isSelected(value) {
        return this.state.includes(value);
    },

    leafsOf(item) {
        return this.flatItems.filter(i => !i.isGroup && this.isDescendantOf(i, item));
    },

    isGroupChecked(item) {
        const leaves = this.leafsOf(item);
        return leaves.length > 0 && leaves.every(i => this.state.includes(i.value));
    },

    isGroupIndeterminate(item) {
        const leaves = this.leafsOf(item);
        const selCount = leaves.filter(i => this.state.includes(i.value)).length;
        return selCount > 0 && selCount < leaves.length;
    },

    toggleItem(value) {
        if (this.state.includes(value)) {
            this.state = this.state.filter(v => v !== value);
        } else {
            this.state = [...this.state, value];
        }
    },

    toggleGroup(item) {
        const values = this.leafsOf(item).map(i => i.value);
        if (this.isGroupChecked(item)) {
            this.state = this.state.filter(v => !values.includes(v));
        } else {
            const toAdd = values.filter(v => !this.state.includes(v));
            this.state = [...this.state, ...toAdd];
        }
    },

    /* ── etichetta pulsante trigger ─────────────────────── */

    get triggerLabel() {
        if (!this.state.length) return @js($placeholder);
        const leaves = this.flatItems.filter(i => !i.isGroup && this.state.includes(i.value));
        return leaves.map(i => i.label).join(', ');
    },
}" @click.outside="open = false" class="relative w-full">
    {{-- ── Trigger ── --}}
    <button type="button" @click="open = !open"
        class="fi-input flex items-center justify-between w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-left min-h-[2.5rem] focus:outline-none focus:ring-2 focus:ring-primary-500">
        <span x-text="triggerLabel" class="truncate text-sm"
            :class="state.length ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'"></span>
        <svg class="w-4 h-4 text-gray-400 shrink-0 ml-2 transition-transform duration-150"
            :class="open ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                clip-rule="evenodd" />
        </svg>
    </button>

    {{-- ── Pannello dropdown ── --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg"
        style="display:none">
        {{-- ── Casella di ricerca (opzionale) ── --}}
        <template x-if="searchable">
            <div class="p-2 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-900 z-10">
                <input type="text" x-model="search" placeholder="Cerca..." @click.stop
                    class="w-full px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>
        </template>

        {{-- ── Lista ad albero ── --}}
        <div class="overflow-y-auto max-h-64 py-1">

            <template x-for="item in flatItems" :key="item.pathKey">
                <div x-show="isVisible(item)">

                    {{-- Nodo GRUPPO --}}
                    <template x-if="item.isGroup">
                        <div class="flex items-center gap-1.5 py-2 pr-3 hover:bg-gray-50 dark:hover:bg-gray-800 select-none"
                            :style="`padding-left: ${8 + item.depth * 16}px`">
                            {{-- freccina apri/chiudi (solo senza ricerca) --}}
                            <button type="button" x-show="!search.trim()" @click.stop="toggleGroupOpen(item.pathKey)"
                                class="shrink-0 w-4 h-4 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                                <svg class="w-3 h-3 transition-transform duration-150"
                                    :class="isGroupOpen(item.pathKey) ? 'rotate-90' : ''"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                            {{-- checkbox di gruppo --}}
                            <label class="flex items-center gap-2 flex-1 cursor-pointer">
                                <input type="checkbox"
                                    class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 cursor-pointer"
                                    :checked="isGroupChecked(item)" @change="toggleGroup(item)"
                                    x-effect="$el.indeterminate = isGroupIndeterminate(item)" />
                                <span x-text="item.label"
                                    class="text-sm font-semibold text-gray-700 dark:text-gray-200"></span>
                            </label>
                        </div>
                    </template>

                    {{-- Nodo FOGLIA --}}
                    <template x-if="!item.isGroup">
                        <label
                            class="flex items-center gap-2 py-2 pr-3 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer"
                            :style="`padding-left: ${8 + item.depth * 16}px`">
                            <input type="checkbox"
                                class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 cursor-pointer"
                                :checked="isSelected(item.value)" @change="toggleItem(item.value)" />
                            <span x-text="item.label" class="text-sm text-gray-700 dark:text-gray-300"></span>
                        </label>
                    </template>

                </div>
            </template>

            {{-- Stato vuoto durante la ricerca --}}
            <div x-show="search.trim() && flatItems.filter(i => isVisible(i)).length === 0"
                class="px-4 py-3 text-sm text-center text-gray-400 dark:text-gray-500">
                Nessun risultato per &ldquo;<span x-text="search"></span>&rdquo;.
            </div>

        </div>
    </div>
</div>
