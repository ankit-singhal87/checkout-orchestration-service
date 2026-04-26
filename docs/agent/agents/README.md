# Narrow Agent Definitions

This folder is the discoverable home for detailed named-agent definitions. Keep [../agents.md](../agents.md) as the roster and operating overview; use this folder when an agent needs durable, narrow rules before production-adjacent work.

## Naming And Modes

Use `agents` as the stable neutral folder name and operating term. It is short, discoverable, already used across the repo, and does not imply gender, rank, or mythology.

Use `Conductor` as the recommended neutral shorthand for the lead orchestrator role when a shorter role name helps. Keep `lead orchestrator` wording available where it makes coordination authority explicit. The Conductor coordinates, routes work, prevents collisions, polls workers, and does not implement work directly when a worker owns the lane. The Conductor never works a worker-owned lane directly; it assigns the lane, monitors it, unblocks it when necessary, and hands it back to the worker or Relay.

The Conductor must use a keep-alive loop while work is pending: poll active workers, command sessions, merge requests, or CI jobs; act on new information; and send brief user-visible progress updates before the session appears idle. Long-running waits should produce concise status every 30 seconds of wall-clock time unless the user has asked for quiet operation.

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
- `observer`: low-cost, read-only branch, merge request, merge, CI, pipeline, or command-output status checks. Observer reports state and blockers, but does not edit files, resolve conflicts, commit, push, prepare MRs, or do other Relay work.

Mode labels do not grant permissions. Autonomy level, allowed paths, branch/worktree assignment, and stop conditions remain authoritative.

## Model Selection

Choose the cheapest model tier that can satisfy the task's risk and complexity, then escalate only when the current tier is insufficient. Cost-tier suggestions are routing hints, not permissions or hard requirements; the Conductor may override them for a specific work package based on ambiguity, blast radius, active failures, context size, or required speed.

- Use a low-cost or small model for `observer` checks, simple read-only branch or MR status, narrow docs formatting, and command-output summarization.
- Use a standard or mid-capability model for bounded `builder` work, Scribe documentation maintenance, routine Relay integration, focused validation follow-up, and ordinary branch hygiene.
- Use a high-capability model for Conductor planning under ambiguity, architecture or security decisions, risky code changes, hard merge conflicts, and root-cause debugging when a worker is blocked.

Do not encode vendor-specific internal model names in agent definitions unless the user has approved that toolchain for the current environment. Prefer tier labels so the policy survives model availability changes.

## Definition Rules

- Every production-adjacent named agent must have a file in this folder before it edits code, coordinates release mechanics, or owns automation that affects other agents.
- Keep each agent narrow enough that multiple named agents can work safely in parallel without editing the same files.
- Prefer splitting a broad role into smaller lane agents over giving one agent a wide ownership surface.
- Every non-read-only worker must use a short-scoped branch such as `agent/<short-scope>` or a more specific approved branch prefix before editing. Parallel non-read-only workers must use separate worktrees.
- Read-only advisory workers may stay on the current worktree when they do not edit files, generate artifacts, commit, push, or coordinate release mechanics.
- A named agent may edit only its assigned allowed paths for the current work package, even if its durable definition lists a broader ownership lane.
- Collision boundaries must identify files, folders, migrations, contracts, or generated artifacts the agent must not touch while another active branch owns them.
- Escalate to the orchestrator when a task crosses the definition, needs a dependency or secret, changes production-adjacent behavior outside the lane, or collides with another active agent branch.
- The Conductor may step into a blocked worker lane only to identify and fix the root cause, then hand the lane back to the worker or to Relay for integration.
- Validated stable slices should move quickly to Relay for commit and, when authorized, MR preparation. Do not hold stable commits locally while unrelated work grows the live context.
- For mutually exclusive independent tasks, the Conductor may run multiple Relay lanes and separate Observers in parallel. Each lane needs its own branch and worktree, distinct writable files, and clear status ownership.

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
- Suggested model cost tier.
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

Suggested model cost tier:

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
