<template>
  <div>
    <div ref="header" class="header-bar"></div>
    <div ref="editor" class="editor"></div>
  </div>
</template>

<script>
  import E from 'wangeditor'

  export default {
    name: 'editor',
    props: {
      value: {
        type: String,
        default: ''
      }
    },
    data() {
      return {
        editor: null
      }
    },
    mounted() {
      this.editor = new E(this.$refs.header, this.$refs.editor)
      this.editor.customConfig = {
        onchange: (html) => {
          this.$emit('input', html)
        },
        menus: [
          'head',  // 标题
          'bold',  // 粗体
          'fontSize',  // 字号
          'fontName',  // 字体
          'italic',  // 斜体
          'underline',  // 下划线
          'strikeThrough',  // 删除线
          'foreColor',  // 文字颜色
          'backColor',  // 背景颜色
          'link',  // 插入链接
          'list',  // 列表
          'justify',  // 对齐方式
          'quote',  // 引用
          'table',  // 表格
          'undo',  // 撤销
          'redo',  // 重复
          'image'
        ],
        uploadImgServer: process.env.VUE_APP_API_HOST + '/upload',
        showLinkImg: false,
        uploadFileName: 'file',
        uploadImgHeaders: {Authorization: this.$store.state.token.type + ' ' + this.$store.state.token.token},
        uploadImgHooks: {
          customInsert: (insert, result) => {
            if (result.code !== 200) {
              this.$message.error(this.$t('upload_error'))
            } else {
              insert(result.data)
            }
          }
        }
      }
      this.editor.create()
      this.editor.txt.html(this.value)
    }
  }
</script>

<style scoped lang="less">
  .header-bar {
    border-radius: 4px 4px 0 0;
    line-height: 16px;
    background: #fff;
    border: 1px solid #dddee1;
    border-bottom: 1px solid #f6f6f6;

    .w-e-menu {
      padding: 2px 10px;
    }
  }

  .editor {
    border-radius: 0 0 4px 4px;
    border: 1px solid #dddee1;
    border-top: none;
    background: #fff;
    height: 200px;
  }
</style>
