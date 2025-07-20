<template>
  <h2 class="mb-4">Student Remark Requests</h2>

  <div class="table-responsive shadow-sm rounded">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th class="p-2 text-left">#</th>
          <th class="p-2 text-left">Request ID</th>
          <th class="p-2 text-left">Student Name</th>
          <th class="p-2 text-left">Student ID</th>
          <th class="p-2 text-left">Course Code</th>
          <th class="p-2 text-left">Section</th>
          <th class="p-2 text-left">Component Name</th>
          <th class="p-2 text-left">Original Mark</th>
          <th class="p-2 text-left">Requested Mark</th>
          <th class="p-2 text-left">Justification</th>
          <th class="p-2 text-left">Status</th>
          <th class="p-2 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="remarkRequests.length === 0" class="text-center text-muted">
          <td colspan="12">No remark requests found.</td>
        </tr>
        <tr v-for="(request, index) in remarkRequests" :key="request.request_id">
          <td class="p-2">{{ index + 1 }}</td>
          <td class="p-2">{{ request.request_id }}</td>
          <td class="p-2">{{ request.student_name }}</td>
          <td class="p-2">{{ request.matric_no }}</td>
          <td class="p-2">{{ request.course_code }}</td>
          <td class="p-2">{{ request.section_number }}</td>
          <td class="p-2">{{ request.component_name }}</td>
          <td class="p-2">{{ request.original_mark }}</td>
          <td class="p-2">{{ request.requested_mark }}</td>
          <td class="p-2">{{ request.justification }}</td>
          <td class="p-2">
            <span :class="getStatusBadgeClass(request.status)">
              {{ request.status }}
            </span>
          </td>
          <td class="p-2 text-center">
            <div class="icon-row">
              <template v-if="request.status === 'Pending'">
                <button @click="handleApprove(request.request_id)" class="btn btn-sm btn-success mx-1">
                  Approve
                </button>
                <button @click="handleReject(request.request_id)" class="btn btn-sm btn-danger mx-1">
                  Reject
                </button>
              </template>
              <template v-else>
                <span class="text-muted">No actions</span>
              </template>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  data() {
    return {
      lecturerId: null,
      remarkRequests: [],
    };
  },
  async created() {
    const user = JSON.parse(sessionStorage.getItem('user'));
    if (!user || !user.lecturerId) {
      alert('Access Denied\nYou must be logged in as a Lecturer to view this page.');
      this.$router.push('/login');
      return;
    }
    this.lecturerId = user.lecturerId;
    await this.fetchRemarkRequests();
  },
  methods: {
    async fetchRemarkRequests() {
      try {
        const url = `http://localhost:3000/remark-requests/lecturer/${this.lecturerId}`;
        const response = await fetch(url);

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        this.remarkRequests = data;
      } catch (error) {
        console.error('Error fetching remark requests:', error);
        alert('Error: Failed to fetch remark requests.');
      }
    },
    async updateRemarkRequestStatus(requestId, newStatus) {
      try {
        const confirmed = confirm(`Are you sure you want to ${newStatus.toLowerCase()} this remark request?`);

        if (!confirmed) {
          return; // User cancelled
        }

        const url = `http://localhost:3000/remark-request/${requestId}`;
        const response = await fetch(url, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            // 'Authorization': `Bearer ${sessionStorage.getItem('jwt')}` // Uncomment when re-enabling auth
          },
          body: JSON.stringify({ status: newStatus }),
        });

        const result = await response.json();

        if (!response.ok) {
          throw new Error(result.error || 'Failed to update remark request.');
        }

        alert(`Request ${newStatus.toLowerCase()} successfully!`);
        await this.fetchRemarkRequests(); // Refresh the list
      } catch (error) {
        console.error('Error updating remark request status:', error);
        alert(`Error: ${error.message || 'Failed to update remark request status.'}`);
      }
    },
    handleApprove(requestId) {
      this.updateRemarkRequestStatus(requestId, 'Approved');
    },
    handleReject(requestId) {
      this.updateRemarkRequestStatus(requestId, 'Rejected');
    },
    getStatusBadgeClass(status) {
      switch (status) {
        case 'Pending':
          return 'badge bg-warning text-dark';
        case 'Approved':
          return 'badge bg-success';
        case 'Rejected':
          return 'badge bg-danger';
        default:
          return 'badge bg-secondary';
      }
    }
  },
};
</script>

<style scoped>
/* Reuse styles from UserPage.vue or define specific ones */
.table-responsive {
  background-color: #fff;
}

.icon-row {
  display: flex;
  justify-content: center;
  gap: 3px;
}

.badge {
  padding: 0.5em 0.7em;
  font-size: 0.85em;
  font-weight: bold;
  border-radius: 0.25rem;
}
</style>