/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/**/*.blade.php',           // Toate fișierele Blade
        './resources/**/*.js',                  // Fișiere JS
        './app/Livewire/**/*.{php,html}',       // Componentele Livewire
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php', // Paginare Laravel
    ],
    // safelist: [
    //     'border-2',        // Adaugă clasele exacte care nu funcționează
    //     'border-gray-500', // Exemplu
    //     'bg-blue-500',     // Exemplu
    //     'bg-slate-600', 
    //     'dark:bg-white',
    //     'border-gray-200', 'dark:border-4', 'border-red-50',
    // ],
    theme: {
        extend: {},
    },
    plugins: [],
}