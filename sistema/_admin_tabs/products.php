<!-- Aba de Produtos -->
<div id="products" class="tab-content section">
    <h2>Gerenciar Produtos</h2>
    <table>
        <thead><tr><th>ID</th><th>Nome</th><th>Preço</th><th>Ações</th></tr></thead>
        <tbody>
            <?php foreach($products as $product): ?>
            <tr>
                <td><?php echo $product['id']; ?></td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td>R$ <?php echo number_format($product['price'], 2, ',', '.'); ?></td>
                <td><a href="product_edit.php?id=<?php echo $product['id']; ?>" class="btn-edit">Editar</a> <a href="handle_product.php?action=delete&id=<?php echo $product['id']; ?>" class="btn-delete" onclick="return confirmDelete();">Excluir</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h3>Adicionar Novo Produto</h3>
    <form action="handle_product.php" method="POST">
        <input type="hidden" name="action" value="create">
        <div class="form-group"><input type="text" name="name" placeholder="Nome do Produto" required></div>
        <div class="form-group"><textarea name="description" placeholder="Descrição do Produto" rows="3"></textarea></div>
        <div class="form-group"><input type="number" step="0.01" name="price" placeholder="Preço (ex: 49.90)" required></div>
        <button type="submit" class="btn btn-save">Adicionar Produto</button>
    </form>
</div>
