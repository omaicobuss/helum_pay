<!-- Aba de Logs de Webhook -->
<div id="logs" class="tab-content section">
    <h2>Logs de Webhook (20 mais recentes) <a href="admin/webhook_logs.php" style="font-size: 14px; float: right;">Ver todos</a></h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Recebido em</th>
                <th>Status</th>
                <th>Detalhes</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($webhook_logs)): ?>
                <tr><td colspan="4" style="text-align: center;">Nenhum log encontrado.</td></tr>
            <?php else: ?>
                <?php foreach ($webhook_logs as $log): ?>
                    <tr>
                        <td><?php echo $log['id']; ?></td>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($log['received_at'])); ?></td>
                        <td>
                            <?php
                            $status = $log['processing_status'] ?? 'PENDING';
                            $class = 'status-info';
                            if (strpos($status, 'SUCCESS') !== false) $class = 'status-success';
                            if (strpos($status, 'FAIL') !== false || strpos($status, 'ERROR') !== false) $class = 'status-failure';
                            if (strpos($status, 'PENDING') !== false || strpos($status, 'VALID') !== false) $class = 'status-pending';
                            ?>
                            <span class="status <?php echo $class; ?>"><?php echo htmlspecialchars($status); ?></span>
                        </td>
                        <td>
                            <span class="details-toggle" onclick="toggleDetails('details-log-<?php echo $log['id']; ?>')">Mostrar/Ocultar</span>
                        </td>
                    </tr>
                    <tr class="details-row">
                        <td colspan="4">
                            <div id="details-log-<?php echo $log['id']; ?>" class="details-content">
                                <strong>Cabeçalhos (Headers):</strong>
                                <pre><?php echo htmlspecialchars(json_encode(json_decode($log['headers']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                <hr>
                                <strong>Parâmetros da URL (Query Params):</strong>
                                <pre><?php echo htmlspecialchars(json_encode(json_decode($log['query_params']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                <hr>
                                <strong>Corpo (Body):</strong>
                                <pre><?php echo htmlspecialchars(json_encode(json_decode($log['body']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
