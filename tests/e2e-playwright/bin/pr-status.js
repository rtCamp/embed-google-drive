#!/usr/bin/env node
// Octokit.js
// https://github.com/octokit/core.js#readme

const { Octokit } = require("@octokit/core");

const octokit = new Octokit({
  auth: process.env.TOKEN,
});

octokit.request("POST /repos/{org}/{repo}/statuses/{sha}", {
  org: "rtCamp",
  repo: "embed-google-drive",
  sha: process.env.SHA ? process.env.SHA : process.env.COMMIT_SHA,
  state: "success",
  conclusion: "success",
  target_url:
    "https://www.tesults.com/results/rsp/view/results/project/8a1c600d-264d-4abc-8b31-0fd651086377",
  description: "Successfully synced to Tesults",
  context: "E2E Test Result",
});