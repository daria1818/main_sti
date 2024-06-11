<?php

(defined('B_PROLOG_INCLUDED') && B_PROLOG_INCLUDED === true) || die();

/**
 * @var $arResult array
 * @var $arParams array
 * @var $component Pwd\Components\FormQrGenerationListComponent
 * @var $this CBitrixComponentTemplate
 */

?>
<?php
if ($arResult['ROWS']) {
    ?>
    <h1>Созданные QR-формы</h1>
    <div class="qr-container">
        <table class="qr">
            <thead>
            <tr>
                <th>ID</th>
                <th>Заголовок</th>
                <th>Описание</th>
                <th>Дата создания</th>
                <th>QR</th>
                <th>Ссылка на форму</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($arResult['ROWS'] as $row) {
                ?>
                <tr>
                    <td><?= $row['ID'] ?></td>
                    <td><?= $row['UF_TITLE'] ?></td>
                    <td><?= $row['UF_DESC'] ?></td>
                    <td><?= $row['UF_DATE'] ?></td>
                    <td><img src="<?= $row['UF_QR'] ?>" alt="">
                    </td>
                    <td><a href="<?= $row['UF_PAGE'] ?>"><?= $row['UF_PAGE'] ?></a></td>
                </tr>
                <?php
            } ?>
            </tbody>
        </table>
    </div>
    <?php
} ?>