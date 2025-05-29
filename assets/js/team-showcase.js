/**
 * Team Showcase Frontend Script - Updated to fetch from WordPress REST API
 */
;(($) => {
  // Main TeamShowcase class
  class TeamShowcase {
    constructor(element) {
      this.container = element
      this.itemsPerPage = Number.parseInt(this.container.dataset.itemsPerPage) || 6
      this.showSearch = this.container.dataset.showSearch !== "false"
      this.showDepartmentFilter = this.container.dataset.showDepartmentFilter !== "false"
      this.selectedDepartment = this.container.dataset.department || "all"

      this.teamMembers = []
      this.filteredMembers = []
      this.departments = []
      this.currentPage = 1
      this.searchQuery = ""
      this.totalPages = 1

      this.init()
    }

    async init() {
      this.renderLoading()
      await this.fetchDepartments()
      await this.fetchTeamMembers()
      this.renderUI()
      this.bindEvents()
    }

    async fetchDepartments() {
      try {
        const response = await fetch(teamShowcaseData.departmentsUrl, {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": teamShowcaseData.nonce,
          },
        })

        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`)
        }

        const departments = await response.json()
        this.departments = [{ id: 0, name: "All Departments", slug: "all" }, ...departments]
      } catch (error) {
        console.error("Error fetching departments:", error)
        this.departments = [{ id: 0, name: "All Departments", slug: "all" }]
      }
    }

    async fetchTeamMembers() {
      try {
        const params = new URLSearchParams({
          per_page: -1, // Get all members, we'll handle pagination on frontend
        })

        if (this.selectedDepartment && this.selectedDepartment !== "all") {
          params.append("department", this.selectedDepartment)
        }

        const response = await fetch(`${teamShowcaseData.apiUrl}?${params}`, {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": teamShowcaseData.nonce,
          },
        })

        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`)
        }

        const data = await response.json()
        this.teamMembers = data.members || []
        this.filteredMembers = [...this.teamMembers]
      } catch (error) {
        console.error("Error fetching team members:", error)
        this.teamMembers = []
        this.filteredMembers = []
      }
    }

    renderLoading() {
      let html = '<div class="team-showcase-loading">'
      html += '<div class="team-showcase-grid">'

      for (let i = 0; i < 6; i++) {
        html += `
                    <div class="team-showcase-card team-showcase-loading-card">
                        <div class="team-showcase-loading-avatar"></div>
                        <div class="team-showcase-loading-line"></div>
                        <div class="team-showcase-loading-line team-showcase-loading-line-short"></div>
                        <div class="team-showcase-loading-social"></div>
                    </div>
                `
      }

      html += "</div></div>"
      this.container.innerHTML = html
    }

    renderUI() {
      let html = '<div class="team-showcase">'

      // Header
      html += `
                <div class="team-showcase-header">
                    <h2 class="team-showcase-title">Meet Our Team</h2>
                    <p class="team-showcase-description">Get to know the talented individuals who make our company great.</p>
                </div>
            `

      // Controls
      html += '<div class="team-showcase-controls">'

      // Search
      if (this.showSearch) {
        html += `
                    <div class="team-showcase-search">
                        <input type="text" placeholder="Search by name or job title..." class="team-showcase-search-input" value="${this.searchQuery}">
                    </div>
                `
      }

      // Department filter and results count
      html += '<div class="team-showcase-filter-row">'

      if (this.showDepartmentFilter) {
        html += '<div class="team-showcase-filter">'
        html += '<label for="team-showcase-department">Filter by department:</label>'
        html += '<select id="team-showcase-department" class="team-showcase-department-select">'

        this.departments.forEach((dept) => {
          const selected = dept.slug === this.selectedDepartment ? "selected" : ""
          html += `<option value="${dept.slug}" ${selected}>${dept.name}</option>`
        })

        html += "</select>"
        html += "</div>"
      }

      // Results count
      html += '<div class="team-showcase-results-count"></div>'
      html += "</div>" // End filter-row

      html += "</div>" // End controls

      // Team grid
      html += '<div class="team-showcase-grid"></div>'

      // Pagination
      html += '<div class="team-showcase-pagination"></div>'

      html += "</div>" // End team-showcase

      this.container.innerHTML = html

      // Render initial team members
      this.renderTeamMembers()
    }

    renderTeamMembers() {
      const grid = this.container.querySelector(".team-showcase-grid")
      const pagination = this.container.querySelector(".team-showcase-pagination")
      const resultsCount = this.container.querySelector(".team-showcase-results-count")

      // Filter members
      this.filterMembers()

      // Calculate pagination
      this.totalPages = Math.ceil(this.filteredMembers.length / this.itemsPerPage)
      const startIndex = (this.currentPage - 1) * this.itemsPerPage
      const endIndex = startIndex + this.itemsPerPage
      const currentMembers = this.filteredMembers.slice(startIndex, endIndex)

      // Update results count
      if (resultsCount) {
        resultsCount.innerHTML = `Showing ${currentMembers.length} of ${this.filteredMembers.length} members`
      }

      // Render team members
      if (currentMembers.length === 0) {
        grid.innerHTML = `
                    <div class="team-showcase-no-results">
                        <p>No team members found. Try adjusting your search or filter.</p>
                    </div>
                `
        pagination.innerHTML = ""
        return
      }

      let html = ""

      currentMembers.forEach((member) => {
        html += `
                    <div class="team-showcase-card" data-member-id="${member.id}">
                        <div class="team-showcase-card-inner">
                            <div class="team-showcase-avatar">
                                <img src="${member.photo}" alt="${member.name}" loading="lazy">
                            </div>
                            <h3 class="team-showcase-name">${member.name}</h3>
                            <p class="team-showcase-job-title">${member.jobTitle}</p>
                            ${member.department ? `<span class="team-showcase-department">${member.department}</span>` : ""}
                            ${member.bio ? `<p class="team-showcase-bio">${member.bio}</p>` : ""}
                            <div class="team-showcase-social">
                                ${member.socialLinks.linkedin ? `<a href="${member.socialLinks.linkedin}" target="_blank" rel="noopener noreferrer" aria-label="${member.name}'s LinkedIn profile" class="team-showcase-social-link team-showcase-linkedin"></a>` : ""}
                                ${member.socialLinks.twitter ? `<a href="${member.socialLinks.twitter}" target="_blank" rel="noopener noreferrer" aria-label="${member.name}'s Twitter profile" class="team-showcase-social-link team-showcase-twitter"></a>` : ""}
                                ${member.contact.email ? `<a href="mailto:${member.contact.email}" aria-label="Email ${member.name}" class="team-showcase-social-link team-showcase-email"></a>` : ""}
                                ${member.contact.phone ? `<a href="tel:${member.contact.phone}" aria-label="Call ${member.name}" class="team-showcase-social-link team-showcase-phone"></a>` : ""}
                            </div>
                        </div>
                    </div>
                `
      })

      grid.innerHTML = html

      // Render pagination
      if (this.totalPages > 1) {
        let paginationHtml = `
                    <button class="team-showcase-pagination-prev" ${this.currentPage === 1 ? "disabled" : ""}>
                        <span>Previous</span>
                    </button>
                    <div class="team-showcase-pagination-numbers">
                `

        // Show page numbers with ellipsis for large page counts
        const maxVisiblePages = 5
        let startPage = Math.max(1, this.currentPage - Math.floor(maxVisiblePages / 2))
        const endPage = Math.min(this.totalPages, startPage + maxVisiblePages - 1)

        if (endPage - startPage < maxVisiblePages - 1) {
          startPage = Math.max(1, endPage - maxVisiblePages + 1)
        }

        if (startPage > 1) {
          paginationHtml += `<button class="team-showcase-pagination-number" data-page="1">1</button>`
          if (startPage > 2) {
            paginationHtml += `<span class="team-showcase-pagination-ellipsis">...</span>`
          }
        }

        for (let i = startPage; i <= endPage; i++) {
          paginationHtml += `
                        <button class="team-showcase-pagination-number ${i === this.currentPage ? "active" : ""}" data-page="${i}">${i}</button>
                    `
        }

        if (endPage < this.totalPages) {
          if (endPage < this.totalPages - 1) {
            paginationHtml += `<span class="team-showcase-pagination-ellipsis">...</span>`
          }
          paginationHtml += `<button class="team-showcase-pagination-number" data-page="${this.totalPages}">${this.totalPages}</button>`
        }

        paginationHtml += `
                    </div>
                    <button class="team-showcase-pagination-next" ${this.currentPage === this.totalPages ? "disabled" : ""}>
                        <span>Next</span>
                    </button>
                `

        pagination.innerHTML = paginationHtml
      } else {
        pagination.innerHTML = ""
      }
    }

    filterMembers() {
      let results = [...this.teamMembers]

      // Filter by department
      if (this.selectedDepartment !== "all") {
        results = results.filter((member) => member.departmentSlug === this.selectedDepartment)
      }

      // Filter by search query
      if (this.searchQuery.trim() !== "") {
        const query = this.searchQuery.toLowerCase()
        results = results.filter(
          (member) =>
            member.name.toLowerCase().includes(query) ||
            member.jobTitle.toLowerCase().includes(query) ||
            (member.bio && member.bio.toLowerCase().includes(query)),
        )
      }

      this.filteredMembers = results
    }

    bindEvents() {
      // Department filter change
      const departmentSelect = this.container.querySelector(".team-showcase-department-select")
      if (departmentSelect) {
        departmentSelect.addEventListener("change", async (e) => {
          this.selectedDepartment = e.target.value
          this.currentPage = 1
          this.renderLoading()
          await this.fetchTeamMembers()
          this.renderTeamMembers()
        })
      }

      // Search input
      const searchInput = this.container.querySelector(".team-showcase-search-input")
      if (searchInput) {
        let searchTimeout
        searchInput.addEventListener("input", (e) => {
          clearTimeout(searchTimeout)
          searchTimeout = setTimeout(() => {
            this.searchQuery = e.target.value
            this.currentPage = 1
            this.renderTeamMembers()
          }, 300) // Debounce search
        })
      }

      // Pagination events - using event delegation
      const pagination = this.container.querySelector(".team-showcase-pagination")
      if (pagination) {
        pagination.addEventListener("click", (e) => {
          // Previous button
          if (e.target.closest(".team-showcase-pagination-prev")) {
            if (this.currentPage > 1) {
              this.currentPage--
              this.renderTeamMembers()
              this.scrollToTop()
            }
          }

          // Next button
          if (e.target.closest(".team-showcase-pagination-next")) {
            if (this.currentPage < this.totalPages) {
              this.currentPage++
              this.renderTeamMembers()
              this.scrollToTop()
            }
          }

          // Page number
          if (e.target.classList.contains("team-showcase-pagination-number")) {
            const page = Number.parseInt(e.target.dataset.page)
            if (page !== this.currentPage) {
              this.currentPage = page
              this.renderTeamMembers()
              this.scrollToTop()
            }
          }
        })
      }
    }

    scrollToTop() {
      this.container.scrollIntoView({ behavior: "smooth", block: "start" })
    }
  }

  // Initialize all team showcase instances on the page
  document.addEventListener("DOMContentLoaded", () => {
    const containers = document.querySelectorAll(".team-showcase-container")
    containers.forEach((container) => {
      new TeamShowcase(container)
    })
  })
})(jQuery)

// Ensure teamShowcaseData is available
var teamShowcaseData = window.teamShowcaseData || {}
