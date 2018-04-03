<div class="users index large-9 medium-8 columns content">
        <?= $this -> Form -> create (
                            "null", [ "type" => "post",
                                      "url" => [ "controller" => "json",
                                                 "action" => "encode" ] ] );
        ?>
        <fieldset>
            <legend><?= __('Encode Json') ?></legend>
            <?php
                echo $this -> Form -> textarea ( "encode", [ "cols" => 10,
                                           "rows" => 2,
                                            "value" => $decode] );
            ?>
        </fieldset>
        <?= $this->Form->button(__('Submit')) ?>
        <?= $this->Form->end() ?>
        <hr>

        <?= $this->Form->create (
                            "null", [ "type" => "post",
                                      "url" => [ "controller" => "json",
                                                 "action" => "decode" ] ] );
        ?>
        <fieldset>
            <legend><?= __('Decode Json') ?></legend>
            <?php
                echo $this -> Form -> textarea ( "decode", [ "cols" => 10,
                                           "rows" => 2,
                                           "value" => $encode] );
            ?>
        </fieldset>
        <?= $this->Form->button(__('Submit')) ?>
        <?= $this->Form->end() ?>

</div>
