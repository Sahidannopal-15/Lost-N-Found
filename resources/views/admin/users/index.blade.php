@extends('layouts.app')

@section('judul', 'Pengguna')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Pengguna</h1>
    </section>

    <div class="panel table-responsive">
        <table class="table tabel-admin">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role }}</td>
                        <td>
                            @if ($user->is_active)
                                Aktif
                            @else
                                Nonaktif
                            @endif
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection