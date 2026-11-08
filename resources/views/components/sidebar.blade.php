<aside
    id="app-sidebar"
    class="fixed inset-y-0 left-0 z-40 w-64 bg-gradient-to-b from-emerald-900 to-emerald-950 text-white shadow-xl transition-transform duration-300 ease-in-out will-change-transform"
    :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
>
    <div class="flex items-center justify-between h-14 px-4 border-b border-emerald-800/50">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-sm font-bold">S</div>
            <span class="text-base font-bold tracking-tight">SIMBA</span>
        </div>
        <button type="button" @click="sidebarOpen = false" aria-label="{{ __('navigation.close_navigation') }}" class="lg:hidden inline-flex min-h-11 min-w-11 items-center justify-center text-white/60 hover:text-white transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
            <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <nav class="p-2 space-y-0.5 overflow-y-auto max-h-[calc(100vh-3.5rem)]">
        @auth
            @php
                $role = auth()->user()->role;
                $route = request()->route()?->getName() ?? '';
            @endphp
            @if($role === 'pengelola_operator')
                <x-sidebar-group
                    :label="__('navigation.groups.main')"
                    icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                    :open="str_starts_with($route, 'pengelola-operator.dashboard')"
                >
                    <x-sidebar-link :active="str_starts_with($route, 'pengelola-operator.dashboard')" href="{{ route('pengelola-operator.dashboard') }}" :label="__('navigation.dashboard')" icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </x-sidebar-group>

                <x-sidebar-group
                    :label="__('navigation.groups.management')"
                    icon="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                    :open="str_starts_with($route, 'pengelola-operator.kategori-mata-pelajaran') || str_starts_with($route, 'pengelola-operator.operator')"
                >
                    <x-sidebar-link :active="str_starts_with($route, 'pengelola-operator.kategori-mata-pelajaran')" href="{{ route('pengelola-operator.kategori-mata-pelajaran.index') }}" :label="__('navigation.subject_categories')" icon="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    <x-sidebar-link :active="str_starts_with($route, 'pengelola-operator.operator')" href="{{ route('pengelola-operator.operator.index') }}" :label="__('navigation.manage_operators')" icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </x-sidebar-group>
            @elseif($role === 'operator')
                <x-sidebar-group
                    :label="__('navigation.groups.main')"
                    icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                    :open="str_starts_with($route, 'operator.dashboard')"
                >
                    <x-sidebar-link :active="str_starts_with($route, 'operator.dashboard')" href="{{ route('operator.dashboard') }}" :label="__('navigation.dashboard')" icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </x-sidebar-group>

                <x-sidebar-group
                    :label="__('navigation.groups.management')"
                    icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                    :open="str_starts_with($route, 'operator.users') || str_starts_with($route, 'operator.kelas') || str_starts_with($route, 'operator.mata-pelajaran') || str_starts_with($route, 'operator.pendaftaran-kelas')"
                >
                    <x-sidebar-link :active="str_starts_with($route, 'operator.users')" href="{{ route('operator.users.index') }}" :label="__('navigation.users')" icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    <x-sidebar-link :active="str_starts_with($route, 'operator.mata-pelajaran')" href="{{ route('operator.mata-pelajaran.index') }}" :label="__('navigation.subjects')" icon="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    <x-sidebar-link :active="str_starts_with($route, 'operator.kelas')" href="{{ route('operator.kelas.index') }}" :label="__('navigation.classes')" icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    <x-sidebar-link :active="str_starts_with($route, 'operator.pendaftaran-kelas')" href="{{ route('operator.pendaftaran-kelas.index') }}" :label="__('navigation.class_registration')" icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </x-sidebar-group>

                <x-sidebar-group
                    :label="__('navigation.groups.content')"
                    icon="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                    :open="str_starts_with($route, 'operator.berita')"
                >
                    <x-sidebar-link :active="str_starts_with($route, 'operator.berita')" href="{{ route('operator.berita.index') }}" :label="__('navigation.news')" icon="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </x-sidebar-group>

                <x-sidebar-group
                    :label="__('navigation.groups.system')"
                    icon="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                    :open="str_starts_with($route, 'operator.import') || str_starts_with($route, 'operator.notifikasi') || str_starts_with($route, 'operator.pengajuan-perubahan-pendidikan')"
                >
                    <x-sidebar-link :active="str_starts_with($route, 'operator.pengajuan-perubahan-pendidikan')" href="{{ route('operator.pengajuan-perubahan-pendidikan.index') }}" :label="__('navigation.education_change_requests')" icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    <x-sidebar-link :active="str_starts_with($route, 'operator.import')" href="{{ route('operator.import') }}" :label="__('navigation.import_data')" icon="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    <x-sidebar-link :active="str_starts_with($route, 'operator.notifikasi')" href="{{ route('operator.notifikasi') }}" :label="__('navigation.notifications')" icon="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </x-sidebar-group>
            @elseif($role === 'pengajar')
                <x-sidebar-group
                    :label="__('navigation.groups.main')"
                    icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                    :open="str_starts_with($route, 'pengajar.dashboard')"
                >
                    <x-sidebar-link :active="str_starts_with($route, 'pengajar.dashboard')" href="{{ route('pengajar.dashboard') }}" :label="__('navigation.dashboard')" icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </x-sidebar-group>

                <x-sidebar-group
                    :label="__('navigation.groups.academic')"
                    icon="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                    :open="str_starts_with($route, 'pengajar.kelas') || str_starts_with($route, 'pengajar.tugas') || str_starts_with($route, 'pengajar.kuis') || str_starts_with($route, 'pengajar.remedial') || str_starts_with($route, 'pengajar.nilai')"
                >
                    <x-sidebar-link :active="str_starts_with($route, 'pengajar.kelas')" href="{{ route('pengajar.kelas.index') }}" :label="__('navigation.classes')" icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    <x-sidebar-link :active="str_starts_with($route, 'pengajar.tugas')" href="{{ route('pengajar.tugas.index') }}" :label="__('navigation.assignments')" icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    <x-sidebar-link :active="str_starts_with($route, 'pengajar.kuis')" href="{{ route('pengajar.kuis.index') }}" :label="__('navigation.quizzes')" icon="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    <x-sidebar-link :active="str_starts_with($route, 'pengajar.remedial')" href="{{ route('pengajar.remedial.index') }}" :label="__('navigation.remedial')" icon="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    <x-sidebar-link :active="str_starts_with($route, 'pengajar.nilai')" href="{{ route('pengajar.nilai') }}" :label="__('navigation.grade_recap')" icon="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </x-sidebar-group>

                <x-sidebar-group
                    :label="__('navigation.groups.ai_analytics')"
                    icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                    :open="str_starts_with($route, 'pengajar.prediksi') || str_starts_with($route, 'pengajar.fitur-siswa') || str_starts_with($route, 'pengajar.ai-settings')"
                >
                    <x-sidebar-link :active="str_starts_with($route, 'pengajar.prediksi')" href="{{ route('pengajar.prediksi') }}" :label="__('navigation.prediction')" icon="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    <x-sidebar-link :active="str_starts_with($route, 'pengajar.fitur-siswa')" href="{{ route('pengajar.fitur-siswa') }}" :label="__('navigation.student_features')" icon="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    <x-sidebar-link :active="str_starts_with($route, 'pengajar.ai-settings')" href="{{ route('pengajar.ai-settings.edit') }}" :label="__('navigation.ai_settings')" icon="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426-1.756-2.924-1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                </x-sidebar-group>

                <x-sidebar-link :active="str_starts_with($route, 'pengajar.notifikasi')" href="{{ route('pengajar.notifikasi') }}" :label="__('navigation.notifications')" icon="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            @elseif($role === 'pelajar')
                <x-sidebar-group
                    :label="__('navigation.groups.main')"
                    icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                    :open="str_starts_with($route, 'pelajar.dashboard')"
                >
                    <x-sidebar-link :active="str_starts_with($route, 'pelajar.dashboard')" href="{{ route('pelajar.dashboard') }}" :label="__('navigation.dashboard')" icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </x-sidebar-group>

                <x-sidebar-group
                    :label="__('navigation.groups.academic')"
                    icon="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                    :open="str_starts_with($route, 'pelajar.kelas') || str_starts_with($route, 'pelajar.tugas') || str_starts_with($route, 'pelajar.kuis') || str_starts_with($route, 'pelajar.nilai') || str_starts_with($route, 'pelajar.remedial') || str_starts_with($route, 'pelajar.pengajuan-pendidikan')"
                >
                    <x-sidebar-link :active="str_starts_with($route, 'pelajar.kelas')" href="{{ route('pelajar.kelas.index') }}" :label="__('navigation.classes')" icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    <x-sidebar-link :active="str_starts_with($route, 'pelajar.tugas')" href="{{ route('pelajar.tugas.index') }}" :label="__('navigation.assignments')" icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    <x-sidebar-link :active="str_starts_with($route, 'pelajar.kuis')" href="{{ route('pelajar.kuis.index') }}" :label="__('navigation.quizzes')" icon="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    <x-sidebar-link :active="str_starts_with($route, 'pelajar.nilai')" href="{{ route('pelajar.nilai') }}" :label="__('navigation.grades')" icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    <x-sidebar-link :active="str_starts_with($route, 'pelajar.pengajuan-pendidikan')" href="{{ route('pelajar.pengajuan-pendidikan.index') }}" :label="__('navigation.grade_level_promotion')" icon="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    <x-sidebar-link :active="str_starts_with($route, 'pelajar.remedial')" href="{{ route('pelajar.remedial.index') }}" :label="__('navigation.remedial')" icon="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </x-sidebar-group>

                <x-sidebar-group
                    :label="__('navigation.groups.ai')"
                    icon="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                    :open="str_starts_with($route, 'pelajar.ai-tutor') || str_starts_with($route, 'pelajar.ai-settings')"
                >
                    <x-sidebar-link :active="str_starts_with($route, 'pelajar.ai-tutor')" href="{{ route('pelajar.ai-tutor') }}" :label="__('navigation.ai_tutor')" icon="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    <x-sidebar-link :active="str_starts_with($route, 'pelajar.ai-settings')" href="{{ route('pelajar.ai-settings.edit') }}" label="Pengaturan AI" icon="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-1.066-2.573c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                </x-sidebar-group>

                <x-sidebar-link :active="str_starts_with($route, 'pelajar.notifikasi')" href="{{ route('pelajar.notifikasi') }}" :label="__('navigation.notifications')" icon="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            @endif
            <div class="border-t border-white/10 pt-2 mt-2">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex min-h-11 items-center gap-3 w-full px-3 py-2 rounded-lg text-sm text-white/60 hover:text-white hover:bg-white/10 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        <svg aria-hidden="true" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        {{ __('common.logout') }}
                    </button>
                </form>
            </div>
        @endauth
    </nav>
</aside>
<div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>
