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
                    <th><span class="visually-hidden">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role }}</td>
                        <td>
                            @include('partials.status', ['status' => $user->is_active ? 'aktif' : 'nonaktif'])
                        </td>
                        <td class="text-end">
                            @if ($user->id === auth()->id())
                                <button type="button" class="btn btn-outline-secondary btn-sm" disabled>Aktifkan/Nonaktifkan</button>
                                <div class="small text-muted">Akun sendiri</div>
                            @else
                                <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="d-inline">
                                    @csrf
                                    @if ($user->is_active)
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Nonaktifkan pengguna ini?');">Nonaktifkan</button>
                                    @else
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">Aktifkan</button>
                                    @endif
                                </form>
                            @endif
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
    <div class="mt-3">
        {{ $users->links() }}
    </div>
@endsection