<input type="checkbox"
       value="{{ optional($contribution->order)->id }}"
       @disabled($contribution->transactions->isNotEmpty())
       class="peer slave absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 sm:left-6">
<div class="absolute inset-y-0 left-0 w-0.5 bg-brand-blue invisible peer-checked:visible"></div>
