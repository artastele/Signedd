# SignED — Combined Workload Profiling Table (Current + Future Features)
### VLE Assignment — Updated with Hearing-Impaired Capstone Features

---

## What Changed from the Original

### Current System (Base LMS)
- Student enrollment & IEP management
- Role-based dashboards (Admin, Teacher, Guidance, Principal, Parent)
- File uploads (PDF assessments, signed IEPs)
- PHPMailer email notifications
- Google OAuth login

### Added Capstone Features (Hearing-Impaired Specific)
- ➕ FSL highlighted vocabulary pop-ups (GIF/short video clips per lesson)
- ➕ AI-generated captions on teacher-uploaded videos (OpenAI Whisper / Google STT API)
- ➕ Smart remedial lesson tracks (rule-based: score < 70% → redirect to remedial)
- ➕ Teacher FSL training module (instructional videos, FSL digital dictionary)
- ➕ High-contrast visual-first dashboard for DHH students
- ➕ Disability-type feature flags (hearing_impaired unlocks extra features)
- ➕ Student quiz system with score tracking
- ➕ Video lesson streaming with caption overlay

---

## Combined Workload Profiling Table

| Resource Dimension | Application Requirement (Combined) | Bare-Metal Specification | Constraint Analysis (Bare-Metal) | Virtualized Instance Specification | Constraint Analysis (Virtualization) | Scaling Options (Both Models) |
|---|---|---|---|---|---|---|
| **CPU** | 4–6 cores (PHP LMS processing + IEP logic + quiz scoring + disability feature flag checks + AI caption API calls + concurrent DHH student sessions) | 16 physical cores | ✅ No bottleneck — 16 cores exceeds 4–6 core demand. However, if AI caption processing is done locally (not via external API), CPU spikes to 8–12 cores during batch video processing. Idle cores wasted on single-service bare-metal. | 12 vCPUs (on host with 32 cores) | ✅ No bottleneck for normal operations. ⚠️ Risk: If AI captioning is processed server-side (local Whisper model), CPU demand spikes sharply during teacher video uploads — may cause contention with co-hosted VMs. | **BM:** Add higher-core CPU for local AI processing (downtime). **Virt:** Increase vCPU allocation during peak upload hours; offload AI captioning to external API (OpenAI/Google) to keep server CPU low. |
| **Memory (RAM)** | 8–16 GB (PHP sessions for concurrent DHH + standard students; MySQL buffer pool for IEP + quiz + FSL vocab data; video file buffering during caption API uploads; FSL animation metadata caching) | 64 GB | ✅ No bottleneck — 64 GB is 4–8× the requirement. Excess RAM cannot be reallocated to other services. Static allocation wastes resources during off-peak school hours. | 48 GB allocated (host has 128 GB total) | ✅ No bottleneck. Minor risk: memory ballooning if other VMs on host compete for the remaining 80 GB. FSL video caching layer benefits significantly from available RAM. | **BM:** Add physical RAM sticks — requires downtime and motherboard slot availability. **Virt:** Increase VM RAM allocation via hypervisor console instantly; host has 80 GB remaining buffer for growth. |
| **Storage (Disk I/O)** | 200–500 GB total breakdown: MySQL DB (IEPs, quizzes, FSL vocab, user data): 5–10 GB; IEP PDF uploads + assessment docs: 10–50 GB; Teacher-uploaded video lessons: 50–150 GB; AI-generated caption files (.vtt/.srt): ~500 MB; FSL vocabulary GIF/clip library (300–500 clips): 1.5–5 GB; Teacher FSL training module videos: 20–80 GB; Remedial lesson content: 5–10 GB; Application logs: 1–2 GB | 2 TB HDD + 512 GB SSD | ⚠️ Critical bottleneck: SSD (512 GB) handles active DB and hot files well. But teacher video uploads + FSL training videos + student lesson streaming hitting the HDD simultaneously creates severe I/O contention. HDD read/write speed (100–150 MB/s) cannot handle concurrent video streaming + DB writes. No RAID = single point of failure for all student and IEP data. | Virtual disk backed by SAN/SSD | ✅ No bottleneck — SAN/SSD backend handles concurrent video reads and DB writes simultaneously without I/O contention. Supports thin provisioning — storage grows on demand. Built-in redundancy protects IEP records and FSL content. | **BM:** Replace HDD with NVMe SSD array in RAID 1/10 — requires downtime and significant hardware cost. **Virt:** Expand virtual disk size via hypervisor dashboard; migrate to faster SAN tier for video-heavy workloads; no downtime required. |
| **Network** | 200–400 Mbps peak (HTTP/HTTPS web traffic for all users; FSL GIF/clip delivery on word clicks: +20–50 Mbps; Video lesson streaming to DHH students at 2–5 Mbps per student × 30 students = 60–150 Mbps; Teacher video uploads for AI captioning: 10–50 Mbps upload; Google STT / OpenAI Whisper API calls: 5–20 Mbps; Teacher FSL training video streaming: 10–30 Mbps; PHPMailer SMTP + Google OAuth: minimal) | 1 Gbps NIC | ⚠️ Severe bottleneck at peak: 1 Gbps = 1000 Mbps theoretical max, but real throughput ~700–800 Mbps. Combined peak demand of 200–400 Mbps leaves very little headroom. During school hours when all DHH students stream video lessons simultaneously with FSL pop-ups active, the NIC approaches saturation. No NIC redundancy — single failure = full outage. | Virtual NIC on 10 Gbps host NIC | ✅ No bottleneck — 10 Gbps provides 10–14× the headroom needed. Virtual NIC handles simultaneous video streaming, FSL clip delivery, and API upload traffic without saturation. Host NIC bonding adds redundancy automatically. | **BM:** Upgrade to 10 Gbps NIC (hardware purchase + installation downtime); add CDN (Cloudflare) to offload FSL clip and video delivery. **Virt:** Increase vNIC bandwidth allocation via hypervisor; implement Cloudflare CDN for static FSL assets — zero infrastructure changes needed. |
| **Bottlenecks Identified** | — | **Bare-Metal:** (1) HDD I/O severely contended during simultaneous video streaming + DB writes + file uploads. (2) 1 Gbps NIC approaches saturation when all DHH students stream video lessons concurrently. (3) Fixed CPU cannot burst for AI caption processing spikes. (4) No snapshot = risky deployments of new FSL features or AI caption integrations. | — | **Virtualization:** (1) CPU contention risk during AI captioning batch jobs if co-hosted VMs are resource-heavy. (2) Memory ballooning under host pressure may delay video buffer loading for DHH students. (3) SAN network latency slightly higher than local NVMe — negligible at school scale but worth monitoring. | — | — |
| **Scaling Strategies** | — | **Bare-Metal:** Vertical scaling only. Replace HDD with NVMe SSD RAID for video I/O. Upgrade 1 Gbps NIC to 10 Gbps. Add RAM for larger MySQL buffer pool. Add CDN in front for FSL video/GIF delivery. Every change requires scheduled downtime — risky during active school terms. Cannot separate app server from DB server without purchasing additional physical hardware. | — | **Virtualization:** (1) Vertical: Increase vCPU/RAM for SignED VM during enrollment periods or when adding new FSL content. (2) Horizontal: Separate MySQL into its own VM (DB isolation); add dedicated media server VM for FSL video and caption file serving. (3) CDN integration (Cloudflare) for static FSL assets and training videos — reduces VM bandwidth load by 60–70%. (4) Snapshot before deploying new AI caption API integration — instant rollback if unstable. (5) Live VM migration during peak school hours for zero-downtime maintenance. | — | — |
| **Final Recommendation** | — | **Virtualization** is the definitive choice for the combined SignED system. The addition of hearing-impaired specific features (FSL animations, AI captions, video lessons, FSL training module) significantly increases storage and network demands, making the limitations of bare-metal deployment (HDD I/O bottleneck, 1 Gbps NIC saturation, no snapshots, vertical-only scaling) critical risks for a production school system. Virtualization resolves all bottlenecks: SAN/SSD backend eliminates I/O contention, 10 Gbps virtual NIC handles peak video streaming, VM snapshots protect against failed AI API integrations, and horizontal scaling allows separation of the app server, database, and media server into isolated VMs — directly supporting RA 10173 compliance for hearing-impaired student PII. | — | — | — |

---
## Reflection Questions

### 1. Which resource dimension is most constrained in bare-metal?

**Storage (Disk I/O)** and **Network** are the most constrained dimensions in the bare-metal setup.
The addition of hearing-impaired specific features—such as FSL (Filipino Sign Language) vocabulary pop-up GIFs, teacher-uploaded video lessons, and a teacher FSL training module—pushes storage requirements to 200–500 GB with heavy concurrent read demand. The 2 TB HDD will bottleneck during simultaneous video streaming and database writes (like AI captioning API data). Furthermore, the 1 Gbps NIC will approach saturation when multiple hearing-impaired students stream video lessons concurrently.

### 2. Which resource dimension is most constrained in virtualization?

**CPU** and **Memory (RAM)** are the most constrained dimensions in virtualization.
Because virtualized resources are shared across the host (which has 32 cores and 128 GB RAM total), the 12 vCPUs and 48 GB of RAM allocated to the SignED instance could face contention. If AI video caption processing (e.g., using OpenAI Whisper) is done server-side, it introduces sharp CPU spikes. Memory ballooning could also occur if other VMs on the same host compete for the remaining resources, potentially causing delays in video buffering for DHH students.

### 3. How do scaling strategies differ between the two models?

- **Bare-Metal:** Scaling is purely **vertical** and hardware-dependent. To resolve the bottlenecks, we would need to physically replace the HDD with an NVMe SSD RAID array and upgrade the 1 Gbps NIC to a 10 Gbps NIC. This requires hardware purchasing, physical installation, and scheduled system downtime, which is risky during active school terms.
- **Virtualization:** Scaling is highly flexible, allowing both **vertical** and **horizontal** scaling without downtime. We can instantly allocate more vCPUs or RAM via the hypervisor console during peak enrollment or video upload periods. Horizontally, we can easily separate the MySQL database and media server into isolated VMs to improve performance and data privacy.

### 4. For your capstone project, which model provides the better fit and why?

**Virtualization** provides a significantly better fit for the SignED capstone project. 
The inclusion of hearing-impaired specific features (FSL video content, concurrent video streaming, AI captions) requires a flexible infrastructure that bare-metal cannot easily provide. Virtualization resolves the critical I/O bottlenecks with a SAN/SSD backend and provides 10 Gbps virtual NICs for ample network headroom. Critically, it allows us to use **VM snapshots** to safely deploy and test complex integrations (like AI speech-to-text APIs) with instant rollback capabilities, and supports isolating sensitive DHH student data into separate VMs to ensure compliance with the RA 10173 Data Privacy Act.
