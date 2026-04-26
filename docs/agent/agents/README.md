# Narrow Agent Definitions

This folder is the discoverable home for detailed named-agent definitions. Keep [../agents.md](../agents.md) as the roster and operating overview; use this folder when an agent needs durable, narrow rules before production-adjacent work.

## Naming And Modes

Use `agents` as the stable neutral folder name and operating term. It is short, discoverable, already used across the repo, and does not imply gender, rank, or mythology.

Use `Conductor` as the recommended neutral shorthand for the lead orchestrator role when a shorter role name helps. Keep `lead orchestrator` wording available where it makes coordination authority explicit. The Conductor coordinates, routes work, prevents collisions, polls workers, and does not implement work directly when a worker owns the lane.

Do not rename this folder to `heroes`, `heroines`, `builders`, or `thinkers`:

- `heroes` and `heroines` add gendered and theatrical framing that does not help routing, review, or accountability.
- Top-level `builders/` and `thinkers/` folders would turn a temporary work mode into a permanent ownership boundary.
- Most useful named agents can change mode by task; the durable boundary should be the ownership lane, allowed paths, validation, and stop conditions.

Use these mode labels inside an agent definition or work package when they clarify execution:

- `thinker`: read-only analysis, architecture review, threat modeling, contract review, or planning.
- `builder`: bounded file edits for implementation, tests, docs, fixtures, scripts, or local tooling.
- `integrator`: branch integration, conflict handling, validation coordination, commit preparation, push, or authorized merge request preparation.
- `steward`: durable project hygiene, context defragmentation, handoff maintenance, docs routing, backlog grooming, or guardrail upkeep.
- `specialist`: narrow domain review or implementation where expertise matters more than broad lane ownership.

Mode labels do not grant permissions. Autonomy level, allowed paths, branch/worktree assignment, and stop conditions remain authoritative.

## Definition Rules

- Every production-adjacent named agent must have a file in this folder before it edits code, coordinates release mechanics, or owns automation that affects other agents.
- Keep each agent narrow enough that multiple named agents can work safely in parallel without editing the same files.
- Prefer splitting a broad role into smaller lane agents over giving one agent a wide ownership surface.
- Every non-read-only worker must use a short-scoped branch such as `agent/<short-scope>` or a more specific approved branch prefix before editing. Parallel non-read-only workers must use separate worktrees.
- Read-only advisory workers may stay on the current worktree when they do not edit files, generate artifacts, commit, push, or coordinate release mechanics.
- A named agent may edit only its assigned allowed paths for the current work package, even if its durable definition lists a broader ownership lane.
- Collision boundaries must identify files, folders, migrations, contracts, or generated artifacts the agent must not touch while another active branch owns them.
- Escalate to the orchestrator when a task crosses the definition, needs a dependency or secret, changes production-adjacent behavior outside the lane, or collides with another active agent branch.

## Required Agent File Shape

Use one Markdown file per agent, named with the stable id, for example `loom-checkout-api.md` or `forge-local-runtime.md`.

Each file must define:

- Name and stable id.
- Mission.
- Default mode or allowed modes.
- Ownership lane.
- Allowed paths.
- Out-of-scope paths.
- Autonomy level.
- Branch naming.
- Expected inputs.
- Expected outputs.
- Validation commands.
- Stop conditions.
- Escalation rules.
- Collision boundaries.

## Template

```markdown
# Agent Name

Stable id:

Mission:

Default mode or allowed modes:

Ownership lane:

Allowed paths:

Out-of-scope paths:

Autonomy level:

Branch naming:

Expected inputs:

Expected outputs:

Validation commands:

Stop conditions:

Escalation rules:

Collision boundaries:
```

## Narrowing Guidance

Use the existing roster names as families, not as automatically broad edit rights. For example, `Loom` can remain the Laravel family, while production-adjacent workers become narrower definitions such as `loom-checkout-api`, `loom-blade-ui`, or `loom-persistence`. `Forge` can split into `forge-local-runtime`, `forge-ci`, and `forge-container-images` when those lanes would otherwise collide. `Relay` should stay focused on branch integration, validation coordination, commits, pushes, and authorized merge request preparation rather than implementation.
