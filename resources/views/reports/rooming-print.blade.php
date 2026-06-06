<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Rooming List') }}</title>
        <style>
            body {
                color: #18181b;
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                margin: 32px;
            }

            h1 {
                font-size: 24px;
                margin: 0 0 4px;
            }

            p {
                color: #52525b;
                margin: 0 0 24px;
            }

            table {
                border-collapse: collapse;
                width: 100%;
            }

            th,
            td {
                border: 1px solid #d4d4d8;
                padding: 10px;
                text-align: left;
                vertical-align: top;
            }

            th {
                background: #f4f4f5;
                font-size: 12px;
                text-transform: uppercase;
            }

            ul {
                margin: 0;
                padding-left: 18px;
            }

            .toolbar {
                display: flex;
                justify-content: flex-end;
                margin-bottom: 16px;
            }

            .toolbar button {
                background: #18181b;
                border: 0;
                border-radius: 6px;
                color: #ffffff;
                cursor: pointer;
                padding: 9px 14px;
            }

            @media print {
                body {
                    margin: 0;
                }

                .toolbar {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="toolbar">
            <button type="button" onclick="window.print()">{{ __('Cetak') }}</button>
        </div>

        <h1>{{ __('Rooming List') }}</h1>
        <p>{{ __('Reuni Alumni Teknik Geodesi UGM Angkatan 1996') }}</p>

        <table>
            <thead>
                <tr>
                    <th>{{ __('Nama Kamar') }}</th>
                    <th>{{ __('Tipe') }}</th>
                    <th>{{ __('Kapasitas') }}</th>
                    <th>{{ __('Penghuni') }}</th>
                    <th>{{ __('Catatan') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rooms as $room)
                    <tr>
                        <td>{{ $room->room_name }}</td>
                        <td>{{ $room->room_type ?: '-' }}</td>
                        <td>{{ $room->assignments_count }}/{{ $room->capacity }}</td>
                        <td>
                            @if ($room->assignments->isNotEmpty())
                                <ul>
                                    @foreach ($room->assignments->sortBy(fn ($assignment) => $assignment->alumni?->full_name ?? '') as $assignment)
                                        <li>{{ $assignment->alumni?->full_name }}{{ $assignment->alumni?->student_number ? ' - '.$assignment->alumni->student_number : '' }}</li>
                                    @endforeach
                                </ul>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            {{ collect([$room->location_notes, $room->notes])->filter()->join(' | ') ?: '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">{{ __('Belum ada data kamar.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>
