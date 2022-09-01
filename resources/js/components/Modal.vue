<template>
    <div dusk="new-relation-modal" class="modal fixed inset-0 z-[60] overflow-x-hidden overflow-y-auto px-3 md:px-0 py-3 md:py-6" tabindex="0"
     role="dialog"
         @click.self="$emit('modal-close')"
         data-modal-open="true" aria-modal="true">
        <div class="relative mx-auto z-20 max-w-screen-md">
            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg shadow-lg overflow-hidden p-8">
                <div class="relative" resource-id=""><!---->
                    <div class="mb-8 space-y-4">
                        <div><h1 class="text-90 font-normal text-xl md:text-2xl mb-3">Aanmaken</h1><!---->
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                                <slot></slot>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center justify-center md:justify-end space-y-2 md:space-y-0 space-x-3">
                        <button size="lg" align="center" component="button" tabindex="0" dusk="cancel-create-button" type="button"
                                @click="$emit('modal-close')"
                                class="appearance-none bg-transparent font-bold text-gray-400 hover:text-gray-300 active:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 dark:active:text-gray-600 dark:hover:bg-gray-800 cursor-pointer rounded text-sm font-bold focus:outline-none focus:ring ring-primary-200 dark:ring-gray-600 inline-flex items-center justify-center h-9 px-3 appearance-none bg-transparent font-bold text-gray-400 hover:text-gray-300 active:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 dark:active:text-gray-600 dark:hover:bg-gray-800">
                            Annuleren
                        </button><!---->
                        <button
                                @click.prevent="$emit('modal-submit')"
                                size="lg" align="center" component="button" dusk="create-button"
                                class="shadow relative bg-primary-500 hover:bg-primary-400 text-white dark:text-gray-900 cursor-pointer rounded text-sm font-bold focus:outline-none focus:ring ring-primary-200 dark:ring-gray-600 inline-flex items-center justify-center h-9 px-3 shadow relative bg-primary-500 hover:bg-primary-400 text-white dark:text-gray-900">
                            <span class="">Aanmaken</span><!----></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
