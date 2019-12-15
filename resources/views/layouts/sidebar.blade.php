<aside id="mainSidebar">
    <ul class="nav flex-column">

        <li class="nav-item {{ request()->path() == 'admin' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('home') }}">
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 20 20">
                        <path d="M10 20a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm-5.6-4.29a9.95 9.95 0 0 1 11.2 0 8 8 0 1 0-11.2 0zm6.12-7.64l3.02-3.02 1.41 1.41-3.02 3.02a2 2 0 1 1-1.41-1.41z"/>
                    </svg>
                </i>
                Home
            </a>
        </li>

        <li class="nav-item {{ request()->path() == 'admin/dashboard' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 20 20">
                        <path d="M10 20a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm-5.6-4.29a9.95 9.95 0 0 1 11.2 0 8 8 0 1 0-11.2 0zm6.12-7.64l3.02-3.02 1.41 1.41-3.02 3.02a2 2 0 1 1-1.41-1.41z"/>
                    </svg>
                </i>
                Dashboard
            </a>
        </li>

        <li class="nav-item {{ request()->path() == 'admin/department' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('department.index') }}">
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 20 20">
                        <path d="M10 20a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm-5.6-4.29a9.95 9.95 0 0 1 11.2 0 8 8 0 1 0-11.2 0zm6.12-7.64l3.02-3.02 1.41 1.41-3.02 3.02a2 2 0 1 1-1.41-1.41z"/>
                    </svg>
                </i>
                Department
            </a>
        </li>


        <li class="nav-item {{ request()->path() == 'admin/program' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('program.index') }}">
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 20 20">
                        <path d="M11 12h6v-1l-3-1V2l3-1V0H3v1l3 1v8l-3 1v1h6v7l1 1 1-1v-7z"/>
                    </svg>
                </i>
                Programs
            </a>
        </li>

        <li class="nav-item {{ request()->path() == 'admin/course' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('course.index') }}">
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 20 20">
                        <path d="M3.33 8L10 12l10-6-10-6L0 6h10v2H3.33zM0 8v8l2-2.22V9.2L0 8zm10 12l-5-3-2-1.2v-6l7 4.2 7-4.2v6L10 20z"/>
                    </svg>
                </i>
                Courses
            </a>
        </li>

        <li class="nav-item {{ request()->path() == 'admin/teacher' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('teacher.index') }}">
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 20 20">
                        <path d="M5 5a5 5 0 0 1 10 0v2A5 5 0 0 1 5 7V5zM0 16.68A19.9 19.9 0 0 1 10 14c3.64 0 7.06.97 10 2.68V20H0v-3.32z"/>
                    </svg>
                </i>
                Teachers
            </a>
        </li>


        <li class="nav-item {{ request()->path() == '' ? 'active' : '' }}">
            <a class="nav-link" href="#">
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 12a6 6 0 1 1 0-12 6 6 0 0 1 0 12zm0-3a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm4 2.75V20l-4-4-4 4v-8.25a6.97 6.97 0 0 0 8 0z"/></svg>
                </i>
                Reports
            </a>
        </li>

        <li class="nav-item {{ request()->path() == '' ? 'active' : '' }}">
            <a class="nav-link" href="#">
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M7.03 2.6a3 3 0 0 1 5.94 0L15 3v1h1a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6c0-1.1.9-2 2-2h1V3l2.03-.4zM5 6H4v12h12V6h-1v1H5V6zm5-2a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg>
                </i>
                Surveys
            </a>
        </li>
    </ul>
</aside>

