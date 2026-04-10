# Filament Tree Dropdown

A Filament component for tree-structured selection (parent → child → grandchild) with checkboxes.
Supports single item selection, group selection and optional text search.

---

## Installation

```bash
composer require passasooz/filament-tree-dropdown
```

Publish the view (optional, to customise it in your project):

```bash
php artisan vendor:publish --provider="Passasooz\FilamentTreeDropdown\FilamentTreeDropdownServiceProvider"
```

---

## Usage

```php
use Passasooz\FilamentTreeDropdown\Forms\Components\TreeDropdown;
```

### Fluent builder

Use `group()` for group nodes and `item()` for leaf nodes.
Methods are chainable and nesting levels are unlimited.

```php
TreeDropdown::make('category')
    ->label('Category')
    ->group('Animals', function ($g) {
        $g->group('Mammals', function ($g) {
            $g->item('Dog', 'dog');
            $g->item('Cat', 'cat');
        });
        $g->group('Birds', function ($g) {
            $g->item('Stork', 'stork');
            $g->item('Parrot', 'parrot');
        });
    })
    ->group('Plants', function ($g) {
        $g->item('Rose', 'rose');
        $g->item('Oak', 'oak');
    })
    ->searchable()   // optional
```

### Raw array

If your data is already structured (e.g. from a DB or API):

```php
TreeDropdown::make('category')
    ->tree([
        [
            'label' => 'Animals',
            'children' => [
                ['label' => 'Dog', 'value' => 'dog'],
                ['label' => 'Cat', 'value' => 'cat'],
            ],
        ],
    ])
    ->searchable()
```

You can change the array keys if your data uses different names:

```php
->labelKey('name')      // default: 'label'
->valueKey('id')        // default: 'value'
->childrenKey('items')  // default: 'children'
```

---

## Options

| Method | Description |
|---|---|
| `->searchable()` | Enables the search bar inside the dropdown |
| `->searchable(false)` | Disables search (default) |
| `->label('...')` | Field label and placeholder text |

### How search works

Search filters by **substring** on the item label (case-insensitive).

- Query `"stork"` → shows all items whose label contains `"stork"`
- Query `"sto"` → shows all items whose label contains `"sto"` (e.g. "Stork" is shown)
- Parent groups are shown automatically if at least one child matches
- Groups with no matching children are hidden

---

## Selection behaviour

- **Leaf node**: toggles the single value.
- **Group node**: selects/deselects all descendant leaf nodes.
  - Checkbox is indeterminate when only some children are selected.
- Groups can be expanded/collapsed with the arrow icon (only when search is inactive).

---

## Development / customisation

- Source files are in `src/Forms/Components/`.
- The view is in `resources/views/tree-dropdown.blade.php`.
- Publish the view with `vendor:publish` to override it in your project.


