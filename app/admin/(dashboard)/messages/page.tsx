import { createClient } from "@/lib/supabase/server";
import { markMessageRead, deleteMessage } from "@/app/admin/actions";

type ContactMessage = {
  id: string;
  full_name: string;
  email_address: string;
  message_body: string;
  created_at: string;
  read: boolean;
};

export default async function AdminMessagesPage() {
  const supabase = await createClient();
  const { data } = await supabase
    .from("contact_messages")
    .select("*")
    .order("created_at", { ascending: false });

  const messages = (data as ContactMessage[]) ?? [];

  return (
    <div className="flex flex-col gap-space-lg">
      <h1 className="font-headline-lg text-headline-lg text-on-surface">Messages</h1>

      <div className="flex flex-col gap-space-sm">
        {messages.length === 0 && (
          <p className="font-body-md text-on-surface-variant">No messages yet.</p>
        )}
        {messages.map((m) => (
          <div
            key={m.id}
            className={`p-space-md rounded-xl shadow-sm flex flex-col gap-space-xs ${
              m.read ? "bg-surface-container-lowest" : "bg-primary-container/10 border border-primary-container"
            }`}
          >
            <div className="flex items-center justify-between gap-space-sm flex-wrap">
              <div>
                <p className="font-label-md text-label-md text-on-surface">
                  {m.full_name}{" "}
                  <span className="font-body-sm text-body-sm text-on-surface-variant">
                    &lt;{m.email_address}&gt;
                  </span>
                </p>
                <p className="font-body-sm text-[11px] text-on-surface-variant">
                  {new Date(m.created_at).toLocaleString()}
                </p>
              </div>
              <div className="flex items-center gap-space-xs">
                <form action={markMessageRead}>
                  <input type="hidden" name="id" value={m.id} />
                  <input type="hidden" name="read" value={(!m.read).toString()} />
                  <button
                    type="submit"
                    className="font-label-md text-label-md text-primary px-space-sm py-space-2xs rounded-full bg-primary/10"
                  >
                    {m.read ? "Mark Unread" : "Mark Read"}
                  </button>
                </form>
                <form action={deleteMessage}>
                  <input type="hidden" name="id" value={m.id} />
                  <button
                    type="submit"
                    className="font-label-md text-label-md text-error px-space-sm py-space-2xs"
                  >
                    Delete
                  </button>
                </form>
              </div>
            </div>
            <p className="font-body-md text-body-md text-on-surface whitespace-pre-wrap">
              {m.message_body}
            </p>
          </div>
        ))}
      </div>
    </div>
  );
}
