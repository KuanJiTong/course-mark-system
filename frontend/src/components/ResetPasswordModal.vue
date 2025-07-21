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
              />
              <div v-if="errorMessage" class="text-danger mt-1">{{ errorMessage }}</div>
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
    visible: Boolean,
    userId: Number,
    loginId: String
  },
  data() {
    return {
      newPassword: '',
      errorMessage: ''
    };
  },
  methods: {
    submit() {
      if (!this.newPassword || this.newPassword.length < 6) {
        this.errorMessage = "Password is required (min 6 characters).";
        return;
      }

      const passwordData = { password: this.newPassword };
      this.$emit('reset-password', passwordData, this.userId);

      this.newPassword = '';
      this.errorMessage = '';
    }
  }
};
</script>

