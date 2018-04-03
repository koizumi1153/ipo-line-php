<nav class="large-3 medium-4 columns" id="actions-sidebar">
</nav>
<div class="users index large-9 medium-8 columns content">
    <h3><?= __($title) ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('kind') ?></th>
                <th scope="col"><?= $this->Paginator->sort('title') ?></th>
                <th scope="col"><?= $this->Paginator->sort('discription') ?></th>
                <th scope="col"><?= $this->Paginator->sort('price') ?></th>
                <th scope="col"><?= $this->Paginator->sort('jan') ?></th>
                <th scope="col"><?= $this->Paginator->sort('img') ?></th>
                <th scope="col"><?= $this->Paginator->sort('date') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($information as $info): ?>
            <tr>
                <td><?= $this->Number->format($info->id) ?></td>
                <td><?= $this->Number->format($info->kind) ?></td>
                <td><?= h($info->title) ?></td>
                <td><?= h($info->discription) ?></td>
                <td><?= $this->Number->format($info->price) ?></td>
                <td><?= $this->Number->format($info->jan) ?></td>
                <td><img src="/img/aqours/<?= h($info->img) ?>"></td>
                <td><?= h($info->date) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
