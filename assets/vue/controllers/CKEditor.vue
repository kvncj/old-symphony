<template>
    <div :id=props.form.id>
        <ckeditor :editor=editor v-model="data" :config="config" :name="props.form.name" :class="props.form.class"
            tag-name="textarea">
        </ckeditor>
    </div>
</template>

<script lang='ts' setup>

import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import CKEditor from '@ckeditor/ckeditor5-vue';
import { ImageUploadAdapter } from './ckeditor/ImageUploaderAdapter';

const props = defineProps<{
    form: {
        id: string,
        class?: string,
        name: string
    },
    data: { type: String, required: false },
    url: { type: String, required: true }
}>()

let data = props.data
const ckeditor = CKEditor.component;
const editor = ClassicEditor
const config = {
    extraPlugins: [ CustomUploadAdapterPlugin ]
}

function CustomUploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = function (loader) {
        return new ImageUploadAdapter(loader, props.url);
    };
}

</script>


