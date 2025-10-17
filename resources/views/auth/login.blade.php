<x-guest-layout>
    <div class="relative min-h-screen flex items-center justify-center bg-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 bg-gray-500 bg-no-repeat bg-cover relative items-center"
        style="background-image: url('{{ asset('assets/images/xbg.jpg') }}');">
        <div class="absolute bg-black opacity-60 inset-0 z-0"></div>
        <div class="max-w-lg w-full space-y-8 p-10 bg-white rounded-xl shadow-lg z-10">
            <div class="grid  gap-8 grid-cols-1">
                <div class="flex flex-col ">
                    <img src="{{ asset('assets/images/tree.gif') }}" class="w-12 mb-2" alt="christmas tree">
                    <div class="flex flex-col sm:flex-row items-center">
                        <h2 class="font-semibold text-lg mr-auto">Iniciar Sesión</h2>
                        <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0"></div>
                    </div>
                    <div>
                        <x-validation-errors class="mb-4" />

                        @session('status')
                            <div class="mb-4 font-medium text-sm text-green-600">
                                {{ $value }}
                            </div>
                        @endsession

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form">
                                <div class="md:flex flex-row md:space-x-4 w-full text-xs mt-6">
                                    <div class="space-y-2 w-full text-xs">
                                        <label class="font-semibold text-gray-600 py-2">DNI</label>
                                        <input placeholder="87654321" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-gray-200 rounded-lg h-10 px-4 text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400" required="required"
                                            type="text" name="dni" id="dni" value="{{ old('dni') }}" autofocus>
                                    </div>
                                </div>
                                <div class="md:flex flex-row md:space-x-4 w-full text-xs mt-6">
                                    <div class="space-y-2 w-full text-xs">
                                        <label class="font-semibold text-gray-600 py-2">Contraseña</label>
                                        <input placeholder="Contraseña" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-gray-200 rounded-lg h-10 px-4 text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400" required="required"
                                            type="password" name="password" id="password" autocomplete="current-password">
                                    </div>
                                </div>

                                <div class="block mt-4">
                                    <label for="remember_me" class="flex items-center">
                                        <x-checkbox id="remember_me" name="remember" />
                                        <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                                    </label>
                                </div>

                                <div class="mt-5 text-right md:space-x-3 md:block flex flex-col-reverse">
                                    <a href="{{ route('user.register.view') }}" class="mb-2 md:mb-0 text-center bg-white px-5 py-2 text-sm shadow-sm font-medium tracking-wider border text-gray-600 rounded-full hover:shadow-lg hover:bg-gray-100">Registrarse</a>
                                    <button type="submit" class="mb-2 md:mb-0 bg-[#146B3A] px-5 py-2 text-sm shadow-sm font-medium tracking-wider text-white rounded-full hover:shadow-lg hover:bg-green-800">Iniciar Sesión</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
