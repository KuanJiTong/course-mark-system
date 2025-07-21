<template>
  <h2 class="mb-4 mt-4">User Management</h2>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2 flex-wrap">
      <button @click="openAddModal" class="mb-2 btn btn-primary">
        Add User
      </button>
      <input
        v-model="searchQuery"
        @input = "searchUser"
        type="text"
        class="form-control"
        placeholder="Search users..."
      />
    </div>
  </div>

  <!-- Role Filter Buttons -->
  <div class="d-flex gap-2 flex-wrap mb-2">
    <button class="btn btn-outline-info" :class="{ active: selectedRole === '' }" @click="filterByRole('')">
      All
    </button>
    <button class="btn btn-outline-info" :class="{ active: selectedRole === 'Admin' }" @click="filterByRole('Admin')">
      Admin
    </button>
    <button class="btn btn-outline-info" :class="{ active: selectedRole === 'Lecturer' }" @click="filterByRole('Lecturer')">
      Lecturer
    </button>
    <button class="btn btn-outline-info" :class="{ active: selectedRole === 'Advisor' }" @click="filterByRole('Advisor')">
      Academic Advisor
    </button>
    <button class="btn btn-outline-info" :class="{ active: selectedRole === 'Student' }" @click="filterByRole('Student')">
      Student
    </button>
  </div>

  <div class="table-responsive shadow-sm rounded">
  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th class="p-2 text-left">#</th>
        <th class="p-2 text-left">Login ID</th>
        <th class="p-2 text-left">Name</th>
        <th class="p-2 text-left">Email</th>
        <th class="p-2 text-left">Faculty</th>
        <th class="p-2 text-left">Role</th>
        <th class="p-2">Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr v-if="users.length === 0 && searchQuery" class="text-center text-muted">
        <td colspan="8">No users found</td>
      </tr>
      <tr v-else-if="users.length === 0" class="text-center text-muted">
        <td colspan="7">No users added yet.</td>
      </tr>
      <tr v-for="(user, index) in filteredUsers" :key="user.userId" class="text-center text-muted">
        <td class="p-2">{{ index + 1 }}</td>
        <td class="p-2 text-start">{{ user.loginId }}</td>
        <td class="p-2 text-start">
          {{ user.title ? user.title + ' ' + user.name : user.name }}
        </td>
        <td class="p-2 text-start">{{ user.email }}</td>
        <td class="p-2">{{ user.facultyAbbreviation }}</td>
        <td class="p-2">
          <ul style="list-style: none; padding-left: 0; margin: 0;">
            <li v-for="(role, index) in user.roleNames" :key="index">
              {{ role }}
            </li>
          </ul>
        </td>
        <td class="p-2 text-center">
          <div class="icon-row">
            <i class="bi bi-pencil-square text-primary mx-2" data-bs-toggle="tooltip" title="Edit" @click="openEditModal(user)"></i>
            <i class="bi bi-lock-fill mx-2" data-bs-toggle="tooltip" title="Reset Password" @click="openResetModal(user)"></i>
            <i class="bi bi-trash-fill text-danger mx-2" data-bs-toggle="tooltip" title="Delete" @click="deleteUser(user.userId)"></i>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
  </div>

  <UserModal
    :show="showModal"
    :userData="selectedUser"
    @close="closeModal"
    @submit-user="handleSubmit"
  />

  <ResetPasswordModal
    :visible="showResetPasswordModal"
    :userId="selectedUserId"
    :loginId="selectedLoginId"
    @reset-password="handleResetPassword"
    @close="closeResetModal"
  />
</template>

<script>
import UserModal from '../components/UserModal.vue'; // Adjust path based on your structure
import ResetPasswordModal from '../components/ResetPasswordModal.vue'; // Adjust path based on your structure


export default {
  components: { 
    UserModal,
    ResetPasswordModal
  },
  data() {
    return {
      showModal: false,
      showResetPasswordModal: false,
      selectedUserId: null,
      selectedLoginId: null,
      selectedUser: {},
      users: [],
      searchQuery: '',
      selectedRole: '', 
    };
  },
  computed: {
    filteredUsers() {
      if (!this.selectedRole) return this.users;

      return this.users.filter(user =>
        user.roleNames && user.roleNames.includes(this.selectedRole)
      );
    }
  },
  async created(){
    await this.fetchAllUsers();
  },
  methods: {
    filterByRole(role) {
      this.selectedRole = role;
    },
    async fetchAllUsers(){
      try {
        const url = `http://localhost:3000/users`;

        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch users');

        const data = await response.json();
        this.users = data; 
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    },
    openAddModal() {
      this.selectedUser = {};
      this.showModal = true;
    },
    openEditModal(user) {
      this.selectedUser = { ...user };
      this.showModal = true;
      console.log(user);
    },
    closeModal() {
      this.showModal = false;
    },
    openResetModal(user) {
      this.selectedUserId = user.userId;
      this.selectedLoginId = user.loginId;
      this.showResetPasswordModal = true;
    },
    closeResetModal() {
      this.selectedUserId = null;
      this.selectedLoginId = null;
      this.showResetPasswordModal  = false;
    },
    async handleSubmit(user) {
      if (user.userId) {
        this.updateUser(user);
      } else {
        await this.addUser(user);
      }
      this.closeModal();
    },
    async addUser(newUser) {
      console.log(newUser);
      // Send POST request
      await fetch('http://localhost:3000/user', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(newUser)
      })
      .then(async response => {
        if (!response.ok) {
          throw new Error('Failed to add user');
        }
        return await response.json();
      })
      .then(data => {
        alert('User added:', data);

        // Fetch the updated course list
        this.fetchAllUsers();
      })
      .catch(error => {
        console.error('Error:', error);
      });
    },
    async updateUser(user){
      try {
        const response = await fetch(`http://localhost:3000/user/${user.userId}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(user),
        });

        if (!response.ok) {
          const error = await response.json();
          console.error('Update failed:', error);
          alert('Failed to update user.');
        } else {
          alert('User updated successfully.');
          this.fetchAllUsers();
        }
      } catch (err) {
        console.error('Request error:', err);
        alert('Network error.');
      }
    },
    async handleResetPassword(passwordData, userId) {
      try {
        const response = await fetch(`http://localhost:3000/user/${userId}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(passwordData),
        });

        if (!response.ok) {
          const error = await response.json();
          console.error('Update failed:', error);
          alert('Failed to change password.');
        } else {
          alert('Password changed successfully.');
          this.fetchAllUsers();
          this.showModal = false;
        }
      } catch (err) {
        console.error('Request error:', err);
        alert('Network error.');
      }
    },
    async searchUser() {
      try {
        const trimmedKeyword = this.searchQuery.trim();
        if (trimmedKeyword === '') {
          await this.fetchAllUsers();
          return;
        }

        const url = `http://localhost:3000/users?keyword=${encodeURIComponent(trimmedKeyword)}`;
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch users');

        const data = await response.json();
        this.users = data;
      } catch (error) {
        console.error('Error searching users:', error);
      }
    },
    async deleteUser(userId) {
      if (!confirm('Are you sure you want to delete this user?')) return;

      try {
        const response = await fetch(`http://localhost:3000/user/${userId}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
          },
        });

        const result = await response.json();

        if (response.ok) {
          alert(result.message || 'User deleted successfully.');
          await this.fetchAllUsers();
        } else {
          alert(result.error || 'Failed to delete user.');
        }
      } catch (error) {
        console.error('Delete error:', error);
        alert('An error occurred while deleting.');
      }
    }
  }
};
</script>

<style scoped>
.icon-row {
  display: flex;
  justify-content: center;
  gap: 3px; 
}

button.active {
  background-color: #0dcaf0
;
  color: white;
}

.bi{
  cursor: pointer;
  width: 25px;
  height: 25px;
  border-radius: 50%;
  background-color: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s ease;
}

.bi:hover {
  background-color: #b5b3b368; 
}

.bi:active {
  transform: scale(0.9); 
  background-color: #e0e0e0; 
}
</style>
