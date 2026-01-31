<template>
    <div>
        <router-view />
    </div>
</template>

<script>
import { App } from '@capacitor/app';
export default {
  name: 'App',

  mounted(){
    App.addListener('backButton', () => {
      if (this.$route.path === '/') {
        const confirmExit = window.confirm('Are you sure you want to exit the app?');
        if (confirmExit) {
          App.exitApp();
        }
      } else {
        this.$router.go(-1);
      }
    });

  }
};
</script>
