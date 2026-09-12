"use client";

import { useState, FormEvent } from "react";

type Status = "idle" | "submitting" | "success" | "error";

export default function ContactForm() {
  const [fullName, setFullName] = useState("");
  const [emailAddress, setEmailAddress] = useState("");
  const [messageBody, setMessageBody] = useState("");
  const [company, setCompany] = useState(""); // honeypot — real users never see or fill this
  const [status, setStatus] = useState<Status>("idle");

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setStatus("submitting");

    try {
      const res = await fetch("/api/contact", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ fullName, emailAddress, messageBody, company }),
      });

      if (!res.ok) throw new Error("Request failed");

      setStatus("success");
      setFullName("");
      setEmailAddress("");
      setMessageBody("");
    } catch {
      setStatus("error");
    }
  }

  return (
    <form className="relative flex flex-col gap-space-md" id="contactForm" onSubmit={handleSubmit}>
      {/* Honeypot field — hidden from sighted users and screen readers alike.
          Bots that blindly fill every input will trip this. */}
      <div className="absolute w-px h-px overflow-hidden opacity-0 -z-10" aria-hidden="true">
        <label htmlFor="company">Company</label>
        <input
          id="company"
          name="company"
          type="text"
          tabIndex={-1}
          autoComplete="off"
          value={company}
          onChange={(e) => setCompany(e.target.value)}
        />
      </div>
      <div className="flex flex-col gap-space-2xs">
        <label className="font-label-md text-label-md text-on-surface-variant" htmlFor="fullName">
          Full Name *
        </label>
        <input
          className="w-full bg-surface-container-low text-on-surface font-body-md text-body-md px-space-md py-space-sm rounded-lg focus:outline-none focus:bg-surface-container-lowest focus:shadow-[0_0_0_2px_#00652c] transition-all"
          id="fullName"
          maxLength={200}
          placeholder="e.g. Kwesi Mensah"
          required
          type="text"
          value={fullName}
          onChange={(e) => setFullName(e.target.value)}
        />
      </div>
      <div className="flex flex-col gap-space-2xs">
        <label className="font-label-md text-label-md text-on-surface-variant" htmlFor="emailAddress">
          Email Address *
        </label>
        <input
          className="w-full bg-surface-container-low text-on-surface font-body-md text-body-md px-space-md py-space-sm rounded-lg focus:outline-none focus:bg-surface-container-lowest focus:shadow-[0_0_0_2px_#00652c] transition-all"
          id="emailAddress"
          maxLength={254}
          placeholder="e.g. kwesi@example.com"
          required
          type="email"
          value={emailAddress}
          onChange={(e) => setEmailAddress(e.target.value)}
        />
      </div>
      <div className="flex flex-col gap-space-2xs">
        <label className="font-label-md text-label-md text-on-surface-variant" htmlFor="messageBody">
          Message *
        </label>
        <textarea
          className="w-full bg-surface-container-low text-on-surface font-body-md text-body-md px-space-md py-space-sm rounded-lg focus:outline-none focus:bg-surface-container-lowest focus:shadow-[0_0_0_2px_#00652c] transition-all resize-none"
          id="messageBody"
          maxLength={5000}
          placeholder="Share details on your packaging requirements, timeline, or partnership inquiries..."
          required
          rows={5}
          value={messageBody}
          onChange={(e) => setMessageBody(e.target.value)}
        />
      </div>
      <div className="pt-space-xs">
        <button
          className="inline-flex items-center justify-center bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md px-space-xl py-space-sm rounded-full transition-all shadow-md active:scale-95 disabled:opacity-60"
          id="submitBtn"
          type="submit"
          disabled={status === "submitting"}
        >
          <span>{status === "submitting" ? "Sending..." : "Send Message"}</span>
          <span className="material-symbols-outlined text-[18px] ml-space-xs">arrow_forward</span>
        </button>
      </div>
      {status === "success" && (
        <div className="font-body-sm text-body-sm text-primary p-space-sm bg-tertiary-fixed rounded-lg transition-all">
          Thank you! Your message has been sent successfully. Our materials engineering team will
          respond within 24 hours.
        </div>
      )}
      {status === "error" && (
        <div className="font-body-sm text-body-sm text-error p-space-sm bg-error-container rounded-lg transition-all">
          Something went wrong sending your message. Please try again or email us directly.
        </div>
      )}
    </form>
  );
}
