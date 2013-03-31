{**
 *  \details &copy; 2011  Open Ximdex Evolution SL [http://www.ximdex.org]
 *
 *  Ximdex a Semantic Content Management System (CMS)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  See the Affero GNU General Public License for more details.
 *  You should have received a copy of the Affero GNU General Public License
 *  version 3 along with Ximdex (see LICENSE file).
 *
 *  If not, visit http://gnu.org/licenses/agpl-3.0.html.
 *
 *  @author Ximdex DevTeam <dev@ximdex.com>
 *  @version $Revision$
 *}

<form method="post" id="mu_action" action="{$action_url}">
        <h2 class="bootstrap_title">Bootstrap project</h2>
        <fieldset class="second_color">
		<legend><span>{t}Create bootstrap project{/t}</span></legend>
		<ol style="width:50%; float:left">
			<li>
				<label for="name" class="aligned">{t}Name(No spaces, please){/t}</label>
				<input type="text" name="name" id="name" value="{$name}" class='cajaxg validable not_empty'/>
			</li>
			<li>
                               <label for="name" class="aligned">{t}Web's Title{/t}</label>
                               <input type="text" name="title" id="title" value="{$name}" class='cajaxg validable not_empty'/>

			</li>
			<li>
				<label for="email" class="aligned">{t}Principal color{/t}</label>
				<input type="text" name="principal_color" id="principal_color" value="#006B6C" class='cajaxg validable not_empty input_colorpicker'/>
			</li>
			<li>
				<label for="password_" class="aligned">{t}Secundary color{/t}</label>
				<input type="text" name='secundary_color' id="secundary_color" value="#ffffff" class='caja validable not_empty input_colorpicker'/>
			</li>
			<li>
				<label for="password_repeated" class="aligned">{t}Font color{/t}</label>
				<input type="text" name='font_color' id="font_color" value="#000000" class='caja validable not_empty input_colorpicker'/>
			</li>
		</ol>
		<div class="bsPreviewContainer" style="width:40%; float:left; clear:right; padding:10px; background-color:#006B6C">
			<div style="min-height:20px; padding:10px; text-align:right;background-color:#ffffff" class="bsPreviewTitle">
				<h4>Title</h4>
			</div>
			<div class="bsPreviewContent" style="padding:10px; color:#000000">
				A Sample text to show the color text.
				Use the form to change the color.
				You will can change all of this in config node, in ximlet folder.
			</div>
		</div>
	</fieldset>
	<fieldset class="buttons-form">
		{button type="reset" label='Reset'}
		{button label="Create project" class='validate' }
	</fieldset>
</form>
