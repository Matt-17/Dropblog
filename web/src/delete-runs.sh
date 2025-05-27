#!/bin/bash

# -----------------------------------------------------
# GitHub Actions Workflow Runs Mass-Deletion Script
# Source: https://qmacro.org/blog/posts/2021/03/26/mass-deletion-of-github-actions-workflow-runs/
#
# Requirements:
#   - gh    (GitHub CLI):      https://cli.github.com/
#   - jq    (JSON processor):  https://stedolan.github.io/jq/
#   - fzf   (Fuzzy finder):    https://github.com/junegunn/fzf
# -----------------------------------------------------

# 🔧 Set your repository details here:
OWNER="your-github-username"
REPO="your-repository-name"
REPO_FULL="$OWNER/$REPO"

# 🔍 Fetch all workflow runs and list them with fzf
SELECTED=$(gh api --paginate "/repos/$REPO_FULL/actions/runs" \
  | jq -r '
    .workflow_runs[] |
    [.id, .name, .conclusion, .created_at] |
    @tsv' \
  | fzf --multi \
        --header="Select workflow runs to delete (TAB to mark, ENTER to confirm)" \
        --preview="echo {}" \
        --preview-window=up:3:wrap)

# ❌ Exit if nothing selected
if [ -z "$SELECTED" ]; then
  echo "No runs selected. Exiting."
  exit 0
fi

# 🗑️ Delete selected runs
echo "$SELECTED" | while IFS=$'\t' read -r ID NAME STATUS DATE; do
  echo "Deleting: [$ID] $NAME ($STATUS)"
  gh api -X DELETE "/repos/$REPO_FULL/actions/runs/$ID"
done

echo "✅ Deletion complete."
