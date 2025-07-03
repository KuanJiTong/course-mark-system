<template>
  <div class="modal fade show d-block" tabindex="-1" v-if="visible" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Reset Password - {{ loginId }}</h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <form @submit.prevent="submit">
          <div class="modal-body">
            <div class="mb-3">
              <label for="newPassword" class="form-label">New Password</label>
              <input
                type="password"
                id="newPassword"
                v-model="newPassword"
                class="form-control"
                required
              />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" @click="$emit('close')">Cancel</button>
            <button type="submit" class="btn btn-success">Reset</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ResetPasswordModal',
  props: {
    visible: {
      type: Boolean,
      required: true
    },
    userId: {
      type: Number,
      required: true
    },
    loginId: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      newPassword: ''
    };
  },
  methods: {
    submit() {
        const passwordData = {password: this.newPassword};
        this.$emit('reset-password', passwordData, this.userId);
        this.newPassword = '';
    }
  }
};
</script>

<style scoped>
/* Optional: Add transitions or animations */
</style>
