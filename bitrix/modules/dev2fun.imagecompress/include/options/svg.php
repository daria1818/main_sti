<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.8.5
 */

/**
 * @var string $optType
 */
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
?>
<tr class="heading">
    <td colspan="2">
        <b><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_SETTINGS', ['#MODULE#' => $optType]) ?></b>
    </td>
</tr>
<tr>
    <td width="40%">
        <label for="enable_<?=$optType?>">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_ENABLE_OPTIMIZE_TO", ['#MODULE#' => $optType]) ?>:
        </label>
    </td>
    <td width="60%">
        <input
            type="checkbox"
            name="common_options[enable_<?=$optType?>]"
            id="enable_<?=$optType?>"
            value="Y"
            <?php
            if (Option::get($curModuleName, "enable_{$optType}", '') === 'Y') {
                echo 'checked';
            }
            ?>
        />
    </td>
</tr>

<tr>
    <td width="40%">
        <label><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_ALGORITHM_SELECT') ?>:</label>
    </td>
    <td width="60%">
        <select name="common_options[opti_algorithm_<?=$optType?>]">
            <?php
            $selectAlgorithm = Option::get($curModuleName, "opti_algorithm_{$optType}", '');
            foreach ($optiAlgorithmList[$optType] as $v) { ?>
                <option value="<?= $v ?>" <?= ($v == $selectAlgorithm ? 'selected' : '') ?>>
                    <?= $v ?>
                </option>
            <?php } ?>
        </select>
    </td>
</tr>

<tr>
    <td width="40%">
        <label for="path_to_node">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_PATH_TO",['#MODULE#'=>'node']) ?>:
        </label>
    </td>
    <td width="60%">
        <input type="text"
               size="50"
               name="common_options[path_to_node]"
               value="<?= Option::get($curModuleName, "path_to_node", '/usr/bin'); ?>"
        /> /node
    </td>
</tr>

<tr>
    <td width="40%">
        <label for="path_to_<?=$optType?>">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_PATH_TO",['#MODULE#'=>$optType]) ?>:
        </label>
    </td>
    <td width="60%">
        <input type="text"
               size="50"
               name="common_options[path_to_<?=$optType?>]"
               value="<?= Option::get($curModuleName, "path_to_{$optType}", '/usr/bin'); ?>"
        /> /svgo
    </td>
</tr>

