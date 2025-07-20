<template>
  <div v-if="show" class="modal d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content rounded-3">
        <div class="modal-header text-center">
          <h5 class="modal-title w-100">{{ isEdit ? 'Edit User' : 'Add User' }}</h5>
          <button type="button" class="btn-close" aria-label="Close" @click="$emit('close')"></button>
        </div>
        <form @submit.prevent="handleSubmit">
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label">Login ID</label>
              <input v-model="user.loginId" type="text" class="form-control" />
              <small class="text-danger" v-if="errors.loginId">{{ errors.loginId }}</small>
            </div>

            <div v-show="!isEdit" class="mb-3">
              <label class="form-label">Role</label>
              <div class="d-flex gap-3 flex-wrap">
                <label v-for="role in roles" :key="role.roleId" class="form-check form-check-inline">
                  <input
                    type="checkbox"
                    class="form-check-input"
                    :value="Number(role.roleId)"
                    v-model.number="user.roleIds"
                  >
                  {{ role.roleName }}
                </label>
              </div>
              <small class="text-danger" v-if="errors.roleIds">{{ errors.roleIds }}</small>
            </div>

            <div v-if="user.roleIds.includes(2)" class="mb-3">
              <label class="form-label">Title</label>
              <select v-model="user.title" class="form-select">
                <option disabled value=''>Select Title</option>
                <option value="Dr.">Dr.</option>
                <option value="Prof.">Prof.</option>
                <option value="Prof. Dr.">Prof. Dr.</option>
                <option value="Assoc. Prof.">Assoc. Prof.</option>
                <option value="Assoc. Prof. Dr.">Assoc. Prof. Dr.</option>
                <option value="Prof. Madya Dr.">Prof. Madya Dr.</option>
                <option value="Ir.">Ir.</option>
                <option value="Ir. Dr.">Ir. Dr.</option>
                <option value="Ts.">Ts.</option>
                <option value="Ts. Dr.">Ts. Dr.</option>
                <option value="Mr.">Mr.</option>
                <option value="Ms.">Ms.</option>
                <option value="Mdm.">Mdm.</option>
                <option value="Dato'">Dato'</option>
                <option value="Datin">Datin</option>
                <option value="Hj.">Hj.</option>
                <option value="Hjh.">Hjh.</option>
              </select>
              <small class="text-danger" v-if="errors.title">{{ errors.title }}</small>
            </div>

            <div class="mb-3">
              <label class="form-label">Name</label>
              <input v-model="user.name" type="text" class="form-control" />
              <small class="text-danger" v-if="errors.name">{{ errors.name }}</small>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input v-model="user.email" type="email" class="form-control" />
              <small class="text-danger" v-if="errors.email">{{ errors.email }}</small>
            </div>

            <div v-if="!isEdit" class="mb-3">
              <label class="form-label">Password</label>
              <input v-model="user.password" type="password" class="form-control" />
              <small class="text-danger" v-if="errors.password">{{ errors.password }}</small>
            </div>

            <div class="mb-3">
              <label class="form-label">Faculty</label>
              <select v-model.number="user.facultyId" class="form-select">
                <option disabled :value="null">Select Faculty</option>
                <option v-for="faculty in faculties" :key="faculty.facultyId" :value="Number(faculty.facultyId)">
                  {{ faculty.facultyName }} ({{ faculty.facultyAbbreviation }})
                </option>
              </select>
              <small class="text-danger" v-if="errors.facultyId">{{ errors.facultyId }}</small>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-danger" @click="$emit('close')">Cancel</button>
            <button type="submit" class="btn btn-primary">
              {{ isEdit ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { getCurrentDateTime } from '../Utils/dateFormatter';

export default {
  props: {
    show: Boolean,
    userData: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      user: {
        loginId: '',
        title: '',
        name: '',
        email: '',
        password: '',
        createdAt: '',
        facultyId: null,
        roleIds: []
      },
      errors: {},
      roles: null,
      faculties: null,
    };
  },
  computed: {
    isEdit() {
      return !!this.userData.userId;
    }
  },
  watch: {
    userData: {
      immediate: true,
      handler(newVal) {
        if (this.isEdit && newVal && Object.keys(newVal).length) {
          this.user = {
            loginId: '',
            title: '',
            name: '',
            email: '',
            password: '',
            createdAt: '',
            facultyId: null,
            roleIds: [],
            ...newVal
          };
          this.user.roleIds = (newVal.roleIds || []).map(id => Number(id));
          this.user.password = '';
        } else {
          this.reset();
        }
        this.errors = {};
      }
    }
  },
  async created() {
    await this.fetchAllfaculties();
    await this.fetchAllRoles();
  },
  methods: {
    async fetchAllfaculties() {
      try {
        const response = await fetch(`http://localhost:3000/faculty`);
        if (!response.ok) throw new Error('Failed to fetch faculties');
        this.faculties = await response.json();
      } catch (error) {
        console.error('Error fetching faculties:', error);
      }
    },
    async fetchAllRoles() {
      try {
        const response = await fetch(`http://localhost:3000/role`);
        if (!response.ok) throw new Error('Failed to fetch roles');
        this.roles = await response.json();
      } catch (error) {
        console.error('Error fetching roles:', error);
      }
    },
    validateEmail(email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(email);
    },
    validate() {
      const errors = {};

      if (!this.user.loginId.trim()) errors.loginId = "Login ID is required.";
      if (!this.user.name.trim()) errors.name = "Name is required.";
      if (!this.user.email.trim()) {
        errors.email = "Email is required.";
      } else if (!this.validateEmail(this.user.email)) {
        errors.email = "Invalid email format.";
      }

      if (!this.isEdit) {
        if (!this.user.password || this.user.password.length < 6) {
          errors.password = "Password is required (min 6 characters).";
        }
      }

      if (!this.user.facultyId) errors.facultyId = "Please select a faculty.";

      const ids = this.user.roleIds;
      if(!this.isEdit){
        if (!this.user.roleIds.length) {
          errors.roleIds = "Select at least one role.";
        } else {
          const valid =
            ids.length === 1 || (ids.length === 2 && ids.includes(2) && ids.includes(3));
          if (!valid) {
            errors.roleIds = 'Only "Lecturer" and "Academic Advisor" can be selected together.';
          }
        }
      }

      if (ids.includes(2) && !this.user.title) {
        errors.title = "Title is required for lecturers.";
      }

      this.errors = errors;
      return Object.keys(errors).length === 0;
    },
    handleSubmit() {
      if (!this.validate()) return;

      if (!this.isEdit) {
        this.user.createdAt = getCurrentDateTime();
      }

      this.$emit('submit-user', { ...this.user });
      this.reset();
    },
    reset() {
      this.user = {
        loginId: '',
        title: '',
        name: '',
        email: '',
        password: '',
        createdAt: '',
        facultyId: null,
        roleIds: []
      };
      this.errors = {};
    }
  }
};
</script>
