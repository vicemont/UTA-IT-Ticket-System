Because we emulate an industry-standard company structure to enforce PR reviews, you cannot push code directly to this repository. You must fork it first.

1. Fork the Repository
Click the Fork button in the top right corner of this repository page to create a personal copy on your own GitHub account.

2. Clone Your Fork Locally
Clone your personal fork (not the acmuta repo) to your machine:

git clone https://github.com/YOUR-USERNAME
/{{REPO}}.git
cd {{REPO}}
3. Add the Upstream Remote (Optional but Recommended)
To keep your fork up to date with the main project:

git remote add upstream https://github.com/{{REPO}}.git
4. Create a Feature Branch
Always work on a focused feature branch. Do not work on main.

git checkout -b feat/my-new-feature
# or fix/my-bug-fix
5. Commit Using Conventional Commits
We use standard prefixes for all commits:

feat: for new features
fix: for bug fixes
docs: for documentation changes
style: for formatting/whitespace changes
refactor: for code restructuring without changing behavior
Example: git commit -m "feat: add user login endpoint"

6. Push and Open a Pull Request
Push your branch to your fork:

git push origin feat/my-new-feature
Then, go to the original repository and click New pull request. Select your fork and branch as the source, and the main branch as the destination.

