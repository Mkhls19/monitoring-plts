@extends('layouts.app')

@section('title', 'System Logs')

@section('content')
<div class="page-header">
    <div>
        <h1>Log Sistem (Event Logs)</h1>
        <p class="subtitle">Catatan aktivitas pengguna dan peringatan sistem</p>
    </div>
</div>

<div class="card" style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--shadow-sm); overflow-x: auto;">
    <table class="display" style="width:100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
        <thead>
            <tr style="border-bottom: 2px solid #e2e8f0; background: #f8fafc;">
                <th style="padding: 12px; color: var(--text-secondary); font-weight: 600;">Waktu</th>
                <th style="padding: 12px; color: var(--text-secondary); font-weight: 600;">Tipe Event</th>
                <th style="padding: 12px; color: var(--text-secondary); font-weight: 600;">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 12px; color: #334155;">{{ $log->timestamp->format('Y-m-d H:i:s') }}</td>
                <td style="padding: 12px;">
                    @if ($log->event_type == 'CRITICAL')
                        <span style="color: var(--accent-red); font-weight: bold;">{{ $log->event_type }}</span>
                    @elseif ($log->event_type == 'LOGIN' || $log->event_type == 'LOGOUT')
                        <span style="color: var(--accent-blue); font-weight: 600;">{{ $log->event_type }}</span>
                    @elseif ($log->event_type == 'CONTROL')
                        <span style="color: var(--accent-yellow); font-weight: 600;">{{ $log->event_type }}</span>
                    @else
                        <span style="color: var(--accent-green); font-weight: 600;">{{ $log->event_type }}</span>
                    @endif
                </td>
                <td style="padding: 12px; color: var(--text-primary);">{{ $log->description }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="padding: 12px; text-align: center; color: var(--text-secondary);">Tidak ada log tersedia.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
