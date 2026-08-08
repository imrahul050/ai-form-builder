<x-app-layout>
    <x-slot name="title">{{ isset($form) ? 'Edit Form: ' . $form->title : 'Form Builder' }}</x-slot>

    <div class="space-y-6">
        @livewire('form-canvas', ['form' => $form ?? null])
    </div>
</x-app-layout>
