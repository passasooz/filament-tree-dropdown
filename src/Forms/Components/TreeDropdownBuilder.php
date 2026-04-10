<?php

namespace Passasooz\FilamentTreeDropdown\Forms\Components;

class TreeDropdownBuilder
{
    protected array $tree = [];
    protected array $current = [];

    public function group(string $label, \Closure $children): static
    {
        $group = [
            'label' => $label,
            'children' => [],
        ];
        $builder = new self();
        $children($builder);
        $group['children'] = $builder->getTree();
        $this->tree[] = $group;
        return $this;
    }

    public function item(string $label, $value): static
    {
        $this->tree[] = [
            'label' => $label,
            'value' => $value,
        ];
        return $this;
    }

    public function getTree(): array
    {
        return $this->tree;
    }
}
