
<html>
    <head>
        <style>
            html, body, p, div, span {
                margin:0in 0in 0in 0in;
                padding:0in 0in 0in 0in;
            }
            .sheet {
                /* PAGE MARGIN */
                padding-top:<?= e(sys_get('ml_page_top_margin')) ?>in;
                padding-left:<?= e(sys_get('ml_page_left_margin')) ?>in;
            }
            .label-wrap {
                display:block;
                float:left;
                width:<?= e(sys_get('ml_label_width')) ?>in; /* <<<< LABEL WIDTH */
                height:<?= e(sys_get('ml_label_height')) ?>in; /* <<<< LABEL HEIGHT */
                margin-right:<?= e(sys_get('ml_page_column_spacing')) ?>in; /* <<<< COLUMN LEFT/RIGHT MARGIN */
                margin-bottom:<?= e(sys_get('ml_page_row_spacing')) ?>in; /* <<<< COLUMN TOP/BOTTOM MARGIN */
                /*outline:1px solid #999;*/
                overflow:hidden;
                text-overflow: ellipsis;
            }
            .label {
                padding:<?= e(sys_get('ml_label_inner_margin')) ?>in; /* <<<< LABEL INNER MARGIN */
                font-size:<?= e(sys_get('ml_label_font_size')) ?>px;
            }
        </style>
    </head>
    <body>
        <?php $index = 0; ?>
        <?php $labels->chunk(sys_get('ml_page_label_count'), function($label_chunk) { ?>
            <div class="sheet">
                <?php foreach($label_chunk as $label): ?>
                    <?php $index++ ?>
                    <div class="label-wrap">
                        <div class="label">
                            <?= dangerouslyUseHTML(string_substituteFromArray(sys_get('ml_label_template'), $label->toMergeTagsForLabel())) ?>
                        </div>
                    </div>
                    <?php if ($index == sys_get('ml_page_label_count')): ?>
                        <div style="page-break-after:always;"></div>
                        <?php $index = 0; ?>
                    <?php endif ?>
                <?php endforeach; ?>
            </div>
        <?php }); ?>
    </body>
</html>
