@props([
    'value',
    'label',
    'property'
])

<button type="button" wire:click="$set('{{ $property }}', '{{ $value }}')"
    {{ $attributes->merge(['class' => 'px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white']) }}>
    {{ $label }}
</button>
