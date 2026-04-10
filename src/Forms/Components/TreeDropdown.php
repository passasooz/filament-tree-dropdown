<?php

namespace Passasooz\FilamentTreeDropdown\Forms\Components;

use Filament\Forms\Components\Field;

class TreeDropdown extends Field
{
    protected string $view = 'filament-tree-dropdown::tree-dropdown';

    protected array $tree = [];
    protected string $labelKey = 'label';
    protected string $valueKey = 'value';
    protected string $childrenKey = 'children';
    protected bool $searchable = false;

    public static function make(string $name = 'tree'): static
    {
        return parent::make($name);
    }

    // ── Builder fluente ────────────────────────────────────────────────────

    /**
     * Aggiunge un nodo gruppo (con figli definiti nella closure).
     */
    public function group(string $label, \Closure $children): static
    {
        $builder = new TreeDropdownBuilder();
        $children($builder);
        $this->tree[] = [
            $this->labelKey    => $label,
            $this->childrenKey => $builder->getTree(),
        ];
        return $this;
    }

    /**
     * Aggiunge un nodo foglia a livello root.
     */
    public function item(string $label, mixed $value): static
    {
        $this->tree[] = [
            $this->labelKey  => $label,
            $this->valueKey  => $value,
        ];
        return $this;
    }

    // ── Approccio array grezzo ─────────────────────────────────────────────

    public function tree(array $tree): static
    {
        $this->tree = $tree;
        return $this;
    }

    public function getTree(): array
    {
        return $this->tree;
    }

    // ── Chiavi ─────────────────────────────────────────────────────────────

    public function labelKey(string $key): static
    {
        $this->labelKey = $key;
        return $this;
    }

    public function getLabelKey(): string
    {
        return $this->labelKey;
    }

    public function valueKey(string $key): static
    {
        $this->valueKey = $key;
        return $this;
    }

    public function getValueKey(): string
    {
        return $this->valueKey;
    }

    public function childrenKey(string $key): static
    {
        $this->childrenKey = $key;
        return $this;
    }

    public function getChildrenKey(): string
    {
        return $this->childrenKey;
    }

    // ── Opzioni ────────────────────────────────────────────────────────────

    public function searchable(bool $condition = true): static
    {
        $this->searchable = $condition;
        return $this;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }
}
