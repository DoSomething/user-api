<?php

namespace Northstar\Http\Presenters;

use Illuminate\Support\HtmlString;

class ForgePresenter
{
    public function field($type, $name, $label, $value = '', $placeholder = '')
    {
        $value = old($name) ?: $value;

        return new HtmlString('
          <div class="form-item">
            <label for="'.e($name).'" class="field-label">'.e($label).'</label>
            <input type="'.e($type).'" class="text-field" name="'.e($name).'" value="'.e($value).'" placeholder="'.e($placeholder).'">
           </div>
        ');
    }
}
