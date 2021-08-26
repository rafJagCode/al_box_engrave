<style>
    .boxGrawerAdminSettings__section{
        margin: 0.5em 0;
        padding: 1em 2em;
        border: 1px solid lightgrey;
        border-radius: 5px;
    }
    .boxGrawerAdminSettings__section-title{
        font-size: 2em;
        font-weight: bold;
        margin: 0 0 1em 0;
    }
    .boxGrawerAdminSettings__check-box{
        margin: 0 0.5em;
    }
</style>
<div id="boxGrawerAdminSettings">

    <div class="boxGrawerAdminSettings__section">
        <div class="boxGrawerAdminSettings__section-title">Włącz opcje grawerowania na stronie produktu</div>
        <div class="boxGrawerAdminSettings__check-box">
            <input
                    type="checkbox"
                    class="boxGrawerAdminSettings__enabled-check-box form-check-input"
                    id="enableBoxGrawer"
                    {if $settings.enabled } checked {/if}
            />
            <label class="form-check-label">Włącz</label>
        </div>
    </div>


    <div class="boxGrawerAdminSettings__section">
        <div class="boxGrawerAdminSettings__section-title">Włącz przypomnienie o zakupie pudełka</div>
        <div class="boxGrawerAdminSettings__check-box">
            <input
                    type="checkbox"
                    class="boxGrawerAdminSettings__reminder-check-box form-check-input"
                    id="reminderBoxGrawer"
                    {if $settings.reminder } checked {/if}
            />
            <label class="form-check-label">Włącz</label>
        </div>
    </div>

    <div class="boxGrawerAdminSettings__section">
        <div class="boxGrawerAdminSettings__section-title">Czcionki</div>
        {foreach from=$availableSettings.fonts item=font}
        <div class="boxGrawerAdminSettings__check-box">
            <input
                    type="checkbox"
                    class="boxGrawerAdminSettings__font-check-box form-check-input"
                    data-value="{$font}"
                    {if empty($settings.fonts) || !in_array($font, $settings.fonts) } checked {/if}
            />
            <label class="form-check-label">{$font}</label>
        </div>
        {/foreach}
    </div>

    <div class="boxGrawerAdminSettings__section">
        <div class="boxGrawerAdminSettings__section-title">Płytki</div>
        {foreach from=$availableSettings.images item=image}
        <div class="boxGrawerAdminSettings__check-box">
            <input
                    type="checkbox"
                    class="boxGrawerAdminSettings__image-check-box form-check-input"
                    data-value="{$image}"
                    {if empty($settings.images) || !in_array($image, $settings.images) } checked {/if}
            />
            <label class="form-check-label">{$image}</label>
        </div>
        {/foreach}
    </div>

    <div class="boxGrawerAdminSettings__section">
        <div class="boxGrawerAdminSettings__section-title">Ikony</div>
        {foreach from=$availableSettings.icons item=icon}
        <div class="boxGrawerAdminSettings__check-box">
            <input
                    type="checkbox"
                    class="boxGrawerAdminSettings__icon-check-box form-check-input"
                    data-value="{$icon}"
                    {if empty($settings.icons) || !in_array($icon, $settings.icons) } checked {/if}
            />
            <label class="form-check-label">{$icon}</label>
        </div>
        {/foreach}
    </div>

    <button id="saveBoxGrawer" type="button" class="btn btn-success">Zapisz</button>
</div>
<script>
    $(document).ready(()=>{
        const saveBoxGrawer = document.getElementById('saveBoxGrawer');

        const getUnselected = (checkboxClassPrefix) => {
            let unselected = [];
            let allCheckboxes = $('.boxGrawerAdminSettings__' + checkboxClassPrefix + '-check-box');
            allCheckboxes.toArray().forEach((checkbox)=> {
                if(!checkbox.checked){
                    unselected.push(checkbox.dataset.value);
                }
            })
            return JSON.stringify(unselected);
        }

        const getSettings = () => {
            let settings = {
                'id_product': '{$settings.id_product}',
                'id_box_grawer': '{$settings.id_box_grawer}',
                'enabled': document.getElementById('enableBoxGrawer').checked,
                'reminder': document.getElementById('reminderBoxGrawer').checked,
                'fonts': getUnselected('font'),
                'images': getUnselected('image'),
                'icons': getUnselected('icon')
            }
            return settings;
        }

        const updateGrawer = async () => {
            let saveButton = $('#saveBoxGrawer');
            saveButton.html('Trwa zapisywanie...');
            $.ajax({
                type: 'POST',
                cache: false,
                dataType: 'json',
                url: 'index.php',
                data: {
                    ajax: 1,
                    controller: 'AdminAlboxgrawer',
                    action: 'updateSettings',
                    token: token,
                    settings: { ...getSettings() }
                },
                success: function (data) {
                },
                complete: function(){
                    saveButton.html('Zapisz');
                }

            });
        }

        saveBoxGrawer.addEventListener('click', updateGrawer);

    })

</script>