<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Patrimônios — OASSAB</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { color: #0052CC; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f0; }
        .meta { color: #666; font-size: 10px; }
    </style>
</head>
<body>
    <h1>Relatório de Patrimônios — OASSAB</h1>
    <p class="meta">Gerado em {{ $geradoEm }}</p>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Valor Aquisição</th>
                <th>Valor Atual</th>
                <th>Data Aquisição</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['codigo'] }}</td>
                    <td>{{ $row['nome'] }}</td>
                    <td>{{ $row['categoria'] }}</td>
                    <td>R$ {{ number_format($row['valor_aquisicao'], 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($row['valor_atual'], 2, ',', '.') }}</td>
                    <td>{{ $row['data_aquisicao'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
