<x-guest-layout>
    <div class="relative min-h-screen flex items-center justify-center bg-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 bg-gray-500 bg-no-repeat bg-cover relative items-center"
        style="background-image: url('{{ asset('assets/images/xbg.jpg') }}');">
        <div class="absolute bg-black opacity-60 inset-0 z-0"></div>
        <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-lg z-10">
            <div class="grid  gap-8 grid-cols-1">
                <div class="flex flex-col ">
                    <div class="flex flex-col sm:flex-row items-center">
                        <h2 class="font-semibold text-lg mr-auto">Bienvenido al Amigo Secreto Familiar</h2>
                        <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0"></div>
                    </div>
                    <div>
                        <div class="form">
                            <div class="md:space-y-2 mb-3">
                                <div class="flex items-center py-6">
                                    <div class="w-12 h-12 mr-4 flex-none rounded-xl border overflow-hidden">
                                        <img class="w-12 h-12 mr-4 object-cover"
                                            src="{{ asset('assets/images/profile.jpg') }}"
                                            alt="Avatar Upload">
                                    </div>
                                    <label class="cursor-pointer ">
                                        <span class="focus:outline-none text-white text-sm py-2 px-4 rounded-full bg-green-400 hover:bg-green-500 hover:shadow-lg">Cambiar</span>
                                        <input type="file" class="hidden" :multiple="multiple" :accept="accept">
                                    </label>
                                </div>
                            </div>
                            <div class="md:flex flex-row md:space-x-4 w-full text-xs">
                                <div class="mb-3 space-y-2 w-full text-xs">
                                    <label class="font-semibold text-gray-600 py-2">Nombre</label>
                                    <input placeholder="Nombre" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-gray-200 rounded-lg h-10 px-4 text-xs placeholder-gray-400" required="required"
                                        type="text" name="integration[shop_name]" id="integration_shop_name">
                                    <p class="text-red text-xs hidden">Please fill out this field.</p>
                                </div>
                                <div class="mb-3 space-y-2 w-full text-xs">
                                    <label class="font-semibold text-gray-600 py-2">DNI</label>
                                    <input placeholder="87965432" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-gray-200 rounded-lg h-10 px-4 text-xs placeholder-gray-400" required="required" type="text"
                                        name="integration[shop_name]" id="integration_shop_name">
                                    <p class="text-red text-xs hidden">Please fill out this field.</p>
                                </div>
                            </div>
                            <div class="mb-3 space-y-2 w-full text-xs">
                                <label class=" font-semibold text-gray-600 py-2">Company Website</label>
                                <div class="flex flex-wrap items-stretch w-full mb-4 relative">
                                    <div class="flex">
                                        <span
                                            class="flex items-center leading-normal bg-grey-lighter border-1 rounded-r-none border border-r-0 border-blue-300 px-3 whitespace-no-wrap text-grey-dark text-sm w-12 h-10 bg-blue-300 justify-center items-center  text-xl rounded-lg text-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </span>
                                    </div>
                                    <input type="text"
                                        class="flex-shrink flex-grow flex-auto leading-normal w-px flex-1 border border-l-0 h-10 border-grey-light rounded-lg rounded-l-none px-3 relative focus:border-blue focus:shadow"
                                        placeholder="https://">
                                </div>
                            </div>
                            <div class="md:flex md:flex-row md:space-x-4 w-full text-xs">
                                <div class="w-full flex flex-col mb-3">
                                    <label class="font-semibold text-gray-600 py-2">Company Address</label>
                                    <input placeholder="Address" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-grey-lighter rounded-lg h-10 px-4" type="text"
                                        name="integration[street_address]" id="integration_street_address">
                                </div>
                                <div class="w-full flex flex-col mb-3">
                                    <label class="font-semibold text-gray-600 py-2">Location</label>
                                    <select class="block w-full bg-grey-lighter text-grey-darker border border-grey-lighter rounded-lg h-10 px-4 md:w-full " required="required" name="integration[city_id]"
                                        id="integration_city_id">
                                        <option value="">Seleted location</option>
                                        <option value="">Cochin,KL</option>
                                        <option value="">Mumbai,MH</option>
                                        <option value="">Bangalore,KA</option>
                                    </select>
                                    <p class="text-sm text-red-500 hidden mt-3" id="error">Please fill out this field.</p>
                                </div>
                            </div>
                            <div class="flex-auto w-full mb-1 text-xs space-y-2">
                                <label class="font-semibold text-gray-600 py-2">Description</label>
                                <textarea required="" name="message" id="" class="w-full min-h-[100px] max-h-[300px] h-28 appearance-none block w-full bg-grey-lighter text-grey-darker border border-grey-lighter rounded-lg  py-4 px-4"
                                    placeholder="Enter your comapny info" spellcheck="false"></textarea>
                                <p class="text-xs text-gray-400 text-left my-3">You inserted 0 characters</p>
                            </div>
                            <p class="text-xs text-red-500 text-right my-3">Required fields are marked with an
                                asterisk <abbr title="Required field">*</abbr>
                            </p>
                            <div class="mt-5 text-right md:space-x-3 md:block flex flex-col-reverse">
                                <button class="mb-2 md:mb-0 bg-white px-5 py-2 text-sm shadow-sm font-medium tracking-wider border text-gray-600 rounded-full hover:shadow-lg hover:bg-gray-100"> Cancel </button>
                                <button class="mb-2 md:mb-0 bg-green-400 px-5 py-2 text-sm shadow-sm font-medium tracking-wider text-white rounded-full hover:shadow-lg hover:bg-green-500">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('css')
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }
    </style>
    @endsection
</x-guest-layout>