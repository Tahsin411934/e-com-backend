@props([
    'id' => 'custom-drawer',
    'overlayId' => 'drawer-overlay',
    'title' => 'Form Window',
    'maxWidth' => 'max-w-lg',
    'submitBtnId' => 'saveBtn',
    'submitBtnText' => 'Save Changes',
    'submitBtnColor' => 'bg-blue-900 hover:bg-blue-700',
    'submitOnClick' => 'saveForm()'
])

<div id="{{ e($overlayId) }}" 
     class="fixed inset-0 bg-black/40 backdrop-blur-sm opacity-0 pointer-events-none z-40 transition-opacity duration-300 ease-in-out">
</div>


<div id="{{ e($id) }}" data-mode="add"
    class="fixed right-0 top-0 h-screen w-full {{ e($maxWidth) }} bg-white dark:bg-gray-800 shadow-2xl z-50 flex flex-col transform translate-x-full transition-transform duration-300 ease-in-out">

    <div class="px-6 py-5 border-b dark:border-gray-700 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-gray-700 dark:to-gray-800 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100" id="drawerTitle">{{ e($title) }}</h2>

        <button type="button" onclick="closeGlobalDrawer('{{ $id }}', '{{ $overlayId }}')"
            class="text-gray-500 dark:text-gray-400 hover:text-red-600 p-1 hover:bg-red-50 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200">
            <i class="fa-solid fa-times text-2xl"></i>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto px-6 py-6">
        {{ $slot }}
    </div>

    <div class="border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-6 py-4 flex gap-3">
        <button type="button" onclick="closeGlobalDrawer('{{ $id }}', '{{ $overlayId }}')"
            class="flex-1 border border-gray-300 dark:border-gray-600 rounded p-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition-colors duration-200">
            Cancel
        </button>

        <button type="button" id="{{ e($submitBtnId) }}" onclick="{{ $submitOnClick }}"
            class="flex-1 {{ e($submitBtnColor) }} text-white rounded p-2 font-medium flex justify-center gap-2 items-center transition-colors duration-200">
            <i class="fa fa-save"></i>
            <span id="drawerButtonText">{{ e($submitBtnText) }}</span>
        </button>
    </div>
</div>
