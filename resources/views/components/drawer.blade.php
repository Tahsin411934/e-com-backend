@props([
    'id' => 'custom-drawer',
    'overlayId' => 'drawer-overlay',
    'title' => 'Form Window',
    'maxWidth' => 'max-w-lg',
    'submitBtnId' => 'saveBtn',
    'submitBtnText' => 'Save Changes',
    'submitBtnColor' => 'bg-primary hover:bg-primary',
    'submitEntity' => 'shared',
    'submitAction' => 'submit',
])

<div id="{{ e($overlayId) }}" 
     class="fixed inset-0 bg-black/40 backdrop-blur-sm opacity-0 pointer-events-none z-40 transition-opacity duration-300 ease-in-out">
</div>


<div id="{{ e($id) }}" data-mode="add"
    class="fixed right-0 top-0 h-[100dvh] w-full max-w-full {{ e($maxWidth) }} bg-white dark:bg-gray-800 shadow-2xl z-50 flex flex-col transform translate-x-full transition-transform duration-300 ease-in-out">

    <div class="shrink-0 px-4 py-4 sm:px-6 sm:py-5 border-b dark:border-gray-700 bg-gradient-to-r from-primary-light to-primary-light dark:from-gray-700 dark:to-gray-800 flex justify-between items-center gap-3">
        <h2 class="min-w-0 break-words text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100" id="drawerTitle">{{ e($title) }}</h2>

        <button type="button"
            data-drawer-id="{{ e($id) }}" data-overlay-id="{{ e($overlayId) }}"
            class="shrink-0 text-gray-500 dark:text-gray-400 hover:text-red-600 p-1 hover:bg-red-50 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200">
            <i class="fa-solid fa-times text-2xl"></i>
        </button>
    </div>

    <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-6 sm:py-6">
        {{ $slot }}
    </div>

    <div class="shrink-0 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] sm:px-6 sm:py-4 flex flex-col-reverse sm:flex-row gap-2 sm:gap-3">
        <button type="button" class="js-global-drawer-close"
            data-drawer-id="{{ e($id) }}" data-overlay-id="{{ e($overlayId) }}"
            class="min-h-10 w-full min-w-0 flex-1 border border-gray-300 dark:border-gray-600 rounded p-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition-colors duration-200">
            Cancel
        </button>

        <button type="button" id="{{ e($submitBtnId) }}"
            class="js-drawer-submit min-h-10 w-full min-w-0 flex-1 {{ e($submitBtnColor) }} text-white rounded p-2 font-medium flex justify-center gap-2 items-center transition-colors duration-200"
            data-crud-entity="{{ e($submitEntity) }}" data-crud-action="{{ e($submitAction) }}">
            <i class="fa fa-save"></i>
            <span id="drawerButtonText">{{ e($submitBtnText) }}</span>
        </button>
    </div>
</div>
