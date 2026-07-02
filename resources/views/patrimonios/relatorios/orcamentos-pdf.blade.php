<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Orçamentos — OASSAB</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { color: #0052CC; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f0; }
    </style>
</head>
<body>
    <h1>Relatório de Orçamentos — OASSAB</h1>
    <p style="color:#666;font-size:10px">Gerado em {{ $geradoEm }}</p>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Prioridade</th>
                <th>Status</th>
                <th>Propostas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orcamentos as $orc)
                <tr>
                    <td>{{ $orc->nome_item }}</td>
                    <td>{{ $orc->prioridade }}</td>
                    <td>{{ $orc->status }}</td>
                    <td>{{ $orc->propostas->count() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
